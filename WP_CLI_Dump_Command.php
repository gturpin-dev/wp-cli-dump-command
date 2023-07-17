<?php

/**
 * The class that define the WP CLI "dump" Command
 */
final class WP_CLI_Dump_Command extends \WP_CLI_Command {
	
	/**
	 * Download a dump of the database
	 * 
	 * ## OPTIONS
	 * 
	 * [--file=<file>]
	 * : The filename of the file to save the dump. If not provided, a default name will be used.
	 * 
	 * ## EXAMPLES
	 * wp dump database
	 * wp dump database --file=custom_dump.sql
	 *
	 * @param array $args The list of arguments
	 * @param array $assoc_args The list of associative arguments
	 *
	 * @return void
	 */
	public function database( array $args, array $assoc_args ) {
		// Assign default values
		$assoc_args = wp_parse_args( $assoc_args, [
			'file' => 'database.sql',
		] );

		// Prepare file and path
		$filename       = sanitize_file_name( $assoc_args['file'] );
		$filename       = date( 'Ymdhis' ) . '-' . $filename;
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