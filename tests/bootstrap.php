<?php
/**
 * WordPress Plugin Loader bootstrap file.
 *
 * @package wp-plugin-loader
 */

/**
 * Visit {@see https://mantle.alley.com/testing/test-framework.html} to learn more.
 */
\Mantle\Testing\manager()
	->maybe_rsync_plugin()
	// Install plugins needed for testing.
	->install_plugin( 'co-authors-plus' )
	->install_plugin( 'shortcake', 'https://github.com/wp-shortcake/shortcake/archive/refs/tags/v0.7.4.zip' )
	->install_plugin( 'wordpress-fieldmanager', 'https://github.com/alleyinteractive/wordpress-fieldmanager/archive/refs/tags/v1.6.1.zip' )
	->install_plugin( 'wp-modified-date-control', 'https://github.com/alleyinteractive/wp-modified-date-control/archive/refs/tags/v1.0.0.zip' )
	->install();
