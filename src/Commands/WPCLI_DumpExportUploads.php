<?php

namespace WPCLI_DumpCommand\Commands;

use WP_CLI;
use WPCLI_DumpCommand\Export\ExportFile;
use WPCLI_DumpCommand\Commands\WPCLI_Command;
use WPCLI_DumpCommand\Exceptions\ExportFailedException;

/**
 * Download a dump of the uploads
 * 
 * ## OPTIONS
 * 
 * [--name=<name>]
 * : The name of the file to save the dump. If not provided, a default name will be used.
 * 
 * ## EXAMPLES
 * wp dump:export uploads
 * wp dump:export uploads --name=custom_dump
 *
 */
final class WPCLI_DumpExportUploads extends WPCLI_Command {

	/**
	 * @inheritDoc
	 */
	protected function register( array $args, array $assoc_args ): void {
		// Assign default values
		$assoc_args = wp_parse_args( $assoc_args, [
			'name' => 'uploads',
		] );

		// Create export file
		try {
			$export_file = new ExportFile( $assoc_args['name'] );
			$export_file->create_from( WP_CONTENT_DIR . '/uploads' );
		} catch ( ExportFailedException $e ) {
			WP_CLI::error( $e->getMessage() );
		}

		WP_CLI::success( sprintf( 'Uploads dumped successfully at "%s".', $export_file->get_dir_path() ) );
	}
}