<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/gturpin-dev
 * 
 * @wordpress-plugin
 * Plugin Name:       WP CLI Dump Command
 * Plugin URI:        https://github.com/gturpin-dev/wp-cli-dump-command
 * Description:       This plugin add a wp-cli command to dump multiple things in a WordPress installation
 * Version:           1.0.0
 * Author:            Guillaume Turpin
 * Author URI:        https://github.com/gturpin-dev
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Requires PHP:      8.1
 * Requires at least: 6.0
 * Text Domain:       wp-cli-dump-command
 * Domain Path:       /languages
 */

use WPCLI_DumpCommand\Plugin;

// Standard plugin security, keep this line in place.
defined( 'ABSPATH' ) || die();

// Load autoloader
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

Plugin::init( __FILE__ );