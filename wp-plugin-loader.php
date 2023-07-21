<?php
/**
 * Plugin Name: WP Plugin Loader
 * Plugin URI: https://github.com/alleyinteractive/wp-plugin-loader
 * Description: Code-enabled WordPress plugin loading
 * Version: 0.1.0
 * Author: Sean Fisher
 * Author URI: https://github.com/alleyinteractive/wp-plugin-loader
 * Requires at least: 5.9
 * Tested up to: 6.2
 *
 * Text Domain: wp-plugin-loader
 * Domain Path: /languages/
 *
 * @package wp-plugin-loader
 */

namespace Alley\WP\WP_Plugin_Loader;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ .'/src/class-wp-plugin-loader.php';
