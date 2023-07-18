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

use WPCLI_DumpCommand\Commands\WPCLI_Dump;
use WPCLI_DumpCommand\Views\Dumps_List_Table;

// Standard plugin security, keep this line in place.
defined( 'ABSPATH' ) || die();

// Load autoloader
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

// Add option page with hello world inside
add_action( 'admin_menu', function () {

	// Create a custom option page in admin menu
	add_menu_page(
		__( 'Custom Dumps', 'wp-cli-dump-command' ),
		__( 'Custom Dumps', 'wp-cli-dump-command' ),
		'manage_options',
		'wp-cli-dump-command',
		function () {
			echo '<div class="wrap">';
			echo '<h1>' . esc_html( get_admin_page_title() ) . '</h1>';

			$list_table = new Dumps_List_Table();
			$list_table->prepare_items();
			$list_table->display();
			echo '</div>';
		},
		'dashicons-database-export',
		85
	);
} );

// Need WP-CLI to register the commands
if ( ( defined( 'WP_CLI' ) && WP_CLI ) ) {
	\WP_CLI::add_command( 'dump', WPCLI_Dump::class );
}