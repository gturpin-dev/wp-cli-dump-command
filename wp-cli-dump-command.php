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
 * Text Domain:       wp-cli-dump-command
 * Domain Path:       /languages
 */

// Standard plugin security, keep this line in place.
defined( 'ABSPATH' ) || die();

// Bail if WP-CLI is not present because the plugin work only with WP-CLI
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) return;

require_once 'WP_CLI_Dump_Command.php';

\WP_CLI::add_command( 'dump', 'WP_CLI_Dump_Command' );