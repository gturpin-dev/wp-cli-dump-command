<?php

namespace WPCLI_DumpCommand\Commands;

use WP_CLI;
use ZipArchive;
use WP_CLI_Command;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use WPCLI_DumpCommand\Export\ExportFile;
use WPCLI_DumpCommand\Exceptions\ExportFailedException;

/**
 * The class that define the WP CLI "dump export" Command
 */
final class WPCLI_DumpExport extends WP_CLI_Command {
	
	/**
	 * Download a dump of the database
	 * 
	 * ## OPTIONS
	 * 
	 * [--name=<name>]
	 * : The name of the file to save the dump. If not provided, a default name will be used.
	 * 
	 * ## EXAMPLES
	 * wp dump database
	 * wp dump database --name=custom_dump
	 *
	 * @param array $args The list of arguments
	 * @param array $assoc_args The list of associative arguments
	 *
	 * @return void
	 */
	public function database( array $args, array $assoc_args ): void {
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

	/**
	 * Download a dump of the themes
	 * 
	 * ## OPTIONS
	 * 
	 * [--name=<name>]
	 * : The name of the file to save the dump. If not provided, a default name will be used.
	 * 
	 * ## EXAMPLES
	 * wp dump themes
	 * wp dump themes --name=custom_dump
	 *
	 * @param array $args The list of arguments
	 * @param array $assoc_args The list of associative arguments
	 *
	 * @return void
	 */
	public function themes( array $args, array $assoc_args ): void {
		// Assign default values
		$assoc_args = wp_parse_args( $assoc_args, [
			'name' => 'themes',
		] );

		// Prepare file and path
		$name           = sanitize_file_name( $assoc_args['name'] );
		$filename       = $name . '_' . date( 'Ymd_his' ) . '.zip';
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

		// Create the zip file
		$zip   = new ZipArchive();
		$files = $this->get_files( WP_CONTENT_DIR . '/themes' );

		// Bail if the zip file couldn't be created
		if ( $zip->open( $dump_file_path, ZipArchive::CREATE ) !== true ) {
			WP_CLI::error( 'Something went wrong while creating the zip file.' );
		}

		// Add all files to the zip file
		foreach ( $files as $file ) {
			$zip->addFile( $file, str_replace( WP_CONTENT_DIR . '/themes/', '', $file ) );
		}

		// Close the zip file
		$zip->close();

		// The file must exist now, otherwise something went wrong
		if ( ! file_exists( $dump_file_path ) ) {
			WP_CLI::error( 'Something went wrong while dumping the themes.' );
		}

		WP_CLI::success( sprintf( 'Themes dumped successfully at "%s".', $dump_file_path ) );
	}

	/**
	 * Download a dump of the plugins
	 * 
	 * ## OPTIONS
	 * 
	 * [--name=<name>]
	 * : The name of the file to save the dump. If not provided, a default name will be used.
	 * 
	 * ## EXAMPLES
	 * wp dump plugins
	 * wp dump plugins --name=custom_dump
	 *
	 * @param array $args The list of arguments
	 * @param array $assoc_args The list of associative arguments
	 *
	 * @return void
	 */
	public function plugins( array $args, array $assoc_args ): void {
		// Assign default values
		$assoc_args = wp_parse_args( $assoc_args, [
			'name' => 'plugins',
		] );

		// Create export file
		try {
			$export_file = new ExportFile( $assoc_args['name'] );
			$export_file->create_from( WP_CONTENT_DIR . '/plugins' );
		} catch ( ExportFailedException $e ) {
			WP_CLI::error( $e->getMessage() );
		}

		WP_CLI::success( sprintf( 'Plugins dumped successfully at "%s".', $export_file->get_dir_path() ) );
	}

	/**
	 * Get all files from a directory
	 *
	 * @param string $directory The directory to scan
	 *
	 * @return array The list of files
	 */
	private function get_files( string $directory ): array {
		$files = [];

		$directory_iterator = new RecursiveDirectoryIterator( $directory, RecursiveDirectoryIterator::SKIP_DOTS );
		$iterator     = new RecursiveIteratorIterator( $directory_iterator, RecursiveIteratorIterator::SELF_FIRST );

		foreach ( $iterator as $file ) {
			if ( $file->isFile() ) {
				$files[] = $file->getPathname();
			}
		}

		return $files;
	}
}