<?php

namespace WPCLI_DumpCommand\Commands;

use WP_CLI;
use WPCLI_DumpCommand\Commands\WPCLI_Command;

/**
 * Download a dump of the database
 * 
 * ## OPTIONS
 * 
 * [--name=<name>]
 * : The name of the file to save the dump. If not provided, a default name will be used.
 * 
 * ## EXAMPLES
 * wp dump:export database
 * wp dump:export database --name=custom_dump
 *
 */
final class WPCLI_DumpExportDatabase extends WPCLI_Command {

	/**
	 * @inheritDoc
	 */
	protected function register( array $args, array $assoc_args ): void {
		// Assign default values
		$assoc_args = wp_parse_args( $assoc_args, [
			'name' => 'database',
		] );

		// Prepare file and path
		$name           = sanitize_file_name( $assoc_args['name'] );
		$filename       = $name . '_' . date( 'Ymd_his' ) . '.sql';
		$dump_dir_path  = WP_CONTENT_DIR . '/dumps';
		$dump_file_path = $dump_dir_path . '/' . $filename;

		// Create the dump directory if it doesn't exist
		if ( ! file_exists( $dump_dir_path ) ) {
			mkdir( $dump_dir_path );
		}

		// Bail if the file already exists
		if ( file_exists( $dump_file_path ) ) {
			WP_CLI::error( sprintf( 'File "%s" already exists. Please choose a different name.', $filename ) );
		}

		// Execute the dump command from wp-cli
		$_command = 'db export ' . escapeshellarg( $dump_file_path );
		WP_CLI::runcommand( $_command, [
			'return'     => true,
			'launch'     => false,
			'exit_error' => false,
		] );

		// The file must exist now, otherwise something went wrong
		if ( ! file_exists( $dump_file_path ) ) {
			WP_CLI::error( 'Something went wrong while dumping the database.' );
		}

		WP_CLI::success( sprintf( 'Database dumped successfully at "%s".', $dump_file_path ) );
	}
}