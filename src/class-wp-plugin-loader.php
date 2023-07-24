<?php
/**
 * WP_Plugin_Loader class file
 *
 * @package wp-plugin-loader
 */

namespace Alley\WP\WP_Plugin_Loader;

/**
 * WordPress Plugin Loader
 */
class WP_Plugin_Loader {
	/**
	 * Cache prefix for APCu caching.
	 *
	 * @var string|null
	 */
	protected ?string $cache_prefix = null;

	/**
	 * Array of loaded plugins.
	 *
	 * @var array<int, string>
	 */
	protected array $loaded_plugins = [];

	/**
	 * Flag to prevent any plugin activations for non-code activated plugins.
	 *
	 * @var bool
	 */
	protected bool $prevent_activations = false;

	/**
	 * Constructor.
	 *
	 * @param array<int, string> $plugins Array of plugins to load.
	 */
	public function __construct( public array $plugins = [] ) {
		if ( did_action( 'plugins_loaded' ) ) {
			trigger_error( // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
				'WP_Plugin_Loader should be instantiated before the plugins_loaded hook.',
				E_USER_WARNING
			);
		}

		$this->load_plugins();

		add_filter( 'plugin_action_links', [ $this, 'filter_plugin_action_links' ], 10, 2 );
		add_filter( 'option_active_plugins', [ $this, 'filter_option_active_plugins' ] );
		add_filter( 'pre_update_option_active_plugins', [ $this, 'filter_pre_update_option_active_plugins' ] );
		add_filter( 'map_meta_cap', [ $this, 'prevent_plugin_activation' ], 10, 2 );
	}

	/**
	 * Prevent any plugin activations for non-code activated plugins.
	 *
	 * @param bool $prevent Whether to prevent activations.
	 * @return static
	 */
	public function prevent_activations( bool $prevent = true ): static {
		$this->prevent_activations = $prevent;

		return $this;
	}

	/**
	 * Enable APCu caching for plugin paths.
	 *
	 * @return static
	 */
	public function enable_caching(): static {
		return $this->set_cache_prefix( 'wp-plugin-loader-' );
	}

	/**
	 * Set the cache prefix for APCu caching.
	 *
	 * @param string|null $prefix The cache prefix.
	 * @return static
	 */
	public function set_cache_prefix( ?string $prefix ): static {
		$this->cache_prefix = function_exists( 'apcu_fetch' ) && filter_var( ini_get( 'apc.enabled' ), FILTER_VALIDATE_BOOLEAN )
			? $prefix
			: null;

		return $this;
	}

	/**
	 * Load the requested plugins.
	 */
	protected function load_plugins(): void {
		$client_mu_plugins = is_dir( WP_CONTENT_DIR . '/client-mu-plugins' );

		foreach ( $this->plugins as $plugin ) {
			if ( file_exists( WP_PLUGIN_DIR . "/$plugin" ) && ! is_dir( WP_PLUGIN_DIR . "/$plugin" ) ) {
				require_once WP_PLUGIN_DIR . "/$plugin";

				$this->loaded_plugins[] = trim( $plugin, '/' );

				continue;
			} elseif ( $client_mu_plugins && file_exists( WP_CONTENT_DIR . "/client-mu-plugins/$plugin" ) && ! is_dir( WP_CONTENT_DIR . "/client-mu-plugins/$plugin" ) ) {
				$plugin = ltrim( $plugin, '/' );

				require_once WP_CONTENT_DIR . "/client-mu-plugins/$plugin";

				continue;
			} elseif ( false === strpos( $plugin, '.php' ) ) {
				// Check the APCu cache if we have a prefix set.
				if ( $this->cache_prefix ) {
					$cached_plugin_path = apcu_fetch( $this->cache_prefix . $plugin );

					if ( false !== $cached_plugin_path ) {
						// Check if the plugin path is valid. If it is, require
						// it. Continue either way if the cache was not false.
						if ( is_string( $cached_plugin_path ) && ! empty( $cached_plugin_path ) ) {
							require_once $cached_plugin_path; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable

							$this->loaded_plugins[] = trim( substr( $cached_plugin_path, strlen( WP_PLUGIN_DIR ) + 1 ), '/' );
						}

						continue;
					}
				}

				// Attempt to locate the plugin by name if it isn't a file.
				$sanitized_plugin = $this->sanitize_plugin_name( $plugin );

				$paths = [
					WP_PLUGIN_DIR . "/$sanitized_plugin/$sanitized_plugin.php",
					WP_PLUGIN_DIR . "/$sanitized_plugin/plugin.php",
					WP_PLUGIN_DIR . "/$sanitized_plugin.php",
				];

				$match = null;

				foreach ( $paths as $path ) {
					if ( file_exists( $path ) ) {
						require_once $path; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable

						$match = true;

						$this->loaded_plugins[] = trim( substr( $path, strlen( WP_PLUGIN_DIR ) + 1 ), '/' );
						break;
					}
				}

				// Bail if we found a match.
				if ( $match ) {
					// Cache the plugin path in APCu if we have a prefix set.
					if ( $this->cache_prefix ) {
						apcu_store( $this->cache_prefix . $plugin, $path );
					}

					continue;
				}
			}

			$error_message = sprintf( 'WP Plugin Loader: Plugin %s not found.', $plugin );

			trigger_error( esc_html( $error_message ), E_USER_WARNING ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error

			if ( extension_loaded( 'newrelic' ) && function_exists( 'newrelic_notice_error' ) ) {
				newrelic_notice_error( $error_message );
			}

			// Bye bye!
			die( esc_html( $error_message ) );
		}
	}

	/**
	 * Ensure code activated plugins are shown as such on core plugins screens
	 *
	 * @param  array<string, string> $actions The existing list of actions.
	 * @param  string                $plugin_file The path to the plugin file.
	 * @return array<string, string>
	 */
	public function filter_plugin_action_links( $actions, $plugin_file ): array {
		$screen = get_current_screen();

		if ( in_array( $plugin_file, $this->loaded_plugins, true ) ) {
			unset( $actions['activate'] );
			unset( $actions['deactivate'] );
			$actions['wp-plugin-loader-code-activated-plugin'] = __( 'Enabled via code', 'wp-plugin-loader' );

			if ( $screen && is_a( $screen, 'WP_Screen' ) && 'plugins' === $screen->id ) {
				unset( $actions['network_active'] );
			}
		} elseif ( $this->prevent_activations ) {
			unset( $actions['activate'] );
			unset( $actions['deactivate'] );
		}

		return $actions;
	}

	/**
	 * Filters the list of active plugins to include the ones we loaded via code.
	 *
	 * @param array<int, string> $value The existing list of active plugins.
	 * @return array<int, string>
	 */
	public function filter_option_active_plugins( $value ): array {
		if ( ! is_array( $value ) ) {
			$value = [];
		}

		$value = array_unique( array_merge( $value, $this->loaded_plugins ) );

		sort( $value );

		return $value;
	}

	/**
	 * Exclude code-active plugins from the database option.
	 *
	 * @param array<int, string> $value The saved list of active plugins.
	 * @return array<int, string>
	 */
	public function filter_pre_update_option_active_plugins( $value ) {
		if ( ! is_array( $value ) ) {
			$value = [];
		}

		$value = array_diff( $value, $this->loaded_plugins );

		sort( $value );

		return $value;
	}

	/**
	 * Helper function to sanitize plugin folder name.
	 *
	 * @param string $folder Folder name.
	 * @return string Sanitized folder name
	 */
	protected function sanitize_plugin_name( string $folder ): string {
		$folder = preg_replace( '#([^a-zA-Z0-9-_.]+)#', '', $folder );
		return str_replace( '..', '', (string) $folder ); // To prevent going up directories.
	}

	/**
	 * Prevent any plugin activations for non-code activated plugins.
	 *
	 * @param array<string> $caps Array of capabilities.
	 * @param string        $cap Capability name.
	 * @return array<string>
	 */
	public function prevent_plugin_activation( $caps, $cap ) {
		if ( $this->prevent_activations && 'activate_plugins' === $cap ) {
			return [ 'do_not_allow' ];
		}

		return $caps;
	}
}
