<?php
/**
 * PluginTest class file
 *
 * @package wp-plugin-loader
 */

namespace Alley\WP\Tests;

use Alley\WP\WP_Plugin_Loader;
use Mantle\Testkit\Test_Case;

/**
 * Test that it can load plugins.
 *
 * All plugins are installed in tests/bootstrap.php.
 */
class PluginTest extends Test_Case {
	/**
	 * Test that it can load a plugin by file.
	 */
	public function test_it_can_load_plugin_by_file(): void {
		$this->assertFalse( class_exists( \CoAuthors_Plus::class ) );

		new WP_Plugin_Loader( [ 'co-authors-plus/co-authors-plus.php' ] );

		$this->assertTrue( class_exists( \CoAuthors_Plus::class ) );
	}

	/**
	 * Test that it can load a plugin by name.
	 */
	public function test_it_can_load_plugin_by_name(): void {
		$this->assertFalse( function_exists( 'shortcode_ui_detection' ) );

		new WP_Plugin_Loader( [ 'shortcake' ] );

		$this->assertTrue( function_exists( 'shortcode_ui_detection' ) );
	}

	/**
	 * Test that it can load multiple plugins.
	 */
	public function test_it_can_conditional_load_plugin(): void {
		$this->assertFalse( class_exists( \Alley\WP\Modified_Date_Control\Modified_Date_Feature::class ) );

		WP_Plugin_Loader::create()->when(
			fn () => false,
			[ 'wp-modified-date-control' ],
		)->load();

		$this->assertFalse( class_exists( \Alley\WP\Modified_Date_Control\Modified_Date_Feature::class ) );

		WP_Plugin_Loader::create()->when(
			fn () => true,
			[ 'wp-modified-date-control' ],
		)->load();

		$this->assertTrue( class_exists( \Alley\WP\Modified_Date_Control\Modified_Date_Feature::class ) );
	}

	/**
	 * Test that _doing_it_wrong() is called when instantiated after plugins_loaded.
	 */
	public function test_it_uses_doing_it_wrong_after_plugins_loaded(): void {
		// Simulate that plugins_loaded has already fired.
		do_action( 'plugins_loaded' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

		$this->expectApplied( 'doing_it_wrong_run' )
			->once()
			->with(
				\Mockery::type( 'string' ),
				'WP_Plugin_Loader should be instantiated before the plugins_loaded hook.',
				\Mockery::type( 'string' )
			);

		new WP_Plugin_Loader( [] );
	}
}
