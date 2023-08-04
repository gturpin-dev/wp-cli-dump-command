<?php

namespace WPCLI_DumpCommand\Export;

use WPCLI_DumpCommand\Utils\ZipFile;
use WPCLI_DumpCommand\Utils\FilenameDumpParser;
use WPCLI_DumpCommand\Exceptions\ZipFailedException;
use WPCLI_DumpCommand\Exceptions\ExportFailedException;
use WPCLI_DumpCommand\Exceptions\FileNotFoundException;
use WPCLI_DumpCommand\Exceptions\BadDumpFilenameException;

final class ExportFile {

	public const EXPORT_PATH = WP_CONTENT_DIR . '/dumps';

	/**
	 * The name of the file to export
	 *
	 * @var string
	 */
	private string $filename;

	/**
	 * Need the name of the file to export
	 * Exclude the extension and every token, date etc. They will be generated automatically
	 * 
	 * @param string $name the name of the file
	 */
	public function __construct( string $name ) {
		$this->filename = $this->generate_filename( $name );
		$this->verify_filename();
	}

	/**
	 * Create the export file from the source path
	 *
	 * @param string $source_path The path of the file to export
	 * 
	 * @throws ExportFailedException If the export fails
	 *
	 * @return bool True if the file has been created successfully
	 */
	public function create_from( string $source_path ): bool {
		// Create the dump directory if it doesn't exist
		if ( ! file_exists( self::EXPORT_PATH ) ) {
			mkdir( self::EXPORT_PATH );
		}

		// Create the zip file
		try {
			ZipFile::create( $this->filename, $source_path, self::EXPORT_PATH );
		} catch ( ZipFailedException $e ) {
			throw new ExportFailedException( $e->getMessage() );
		}

		// The file must exist now, otherwise something went wrong
		if ( ! file_exists( self::EXPORT_PATH . '/' . $this->filename ) ) {
			throw new ExportFailedException( sprintf( 'The export file "%s" has not been created.', self::EXPORT_PATH . '/' . $this->filename ) );
		}

		return true;
	}

	/**
	 * Generate the filename of the export file based on the name and the current date
	 *
	 * @param stirng $name The base name of the file to help generate the filename
	 * 
	 * @return string The generated filename
	 */
	private function generate_filename( string $name ): string {
		$name     = sanitize_file_name( $name );
		$filename = $name . '_' . date( 'Ymd_his' ) . '.zip';

		return $filename;
	}

	/**
	 * Verify the filename to ensure it's valid before exporting it.
	 *
	 * @return void
	 */
	private function verify_filename() {
		try {
			new FilenameDumpParser( self::EXPORT_PATH . $this->filename );
		} catch ( BadDumpFilenameException $e ) {
			throw new ExportFailedException( $e->getMessage() );
		}
	}

	/**
	 * Delete the export file based on the filename
	 * 
	 * @param string $filename The name of the file to delete
	 *
	 * @return void
	 */
	public static function delete( string $filename ) {
		unlink( self::EXPORT_PATH . '/' . $filename );
	}

	public static function download( string $filename ) {
		$file = self::EXPORT_PATH . '/' . $filename;

		if ( ! file_exists( $file ) ) {
			throw new FileNotFoundException( sprintf( 'The export file "%s" does not exist.', $file ) );
		}

		header( 'Content-Description: File Transfer' );
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename="' . basename( $file ) . '"' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate' );
		header( 'Pragma: public' );
		header( 'Content-Length: ' . filesize( $file ) );

		readfile( $file );
		exit;
	}

	/**
	 * Get the directory path of the export file
	 *
	 * @return string
	 */
	public function get_dir_path(): string {
		return self::EXPORT_PATH;
	}
}