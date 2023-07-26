<?php

namespace WPCLI_DumpCommand\Utils;

use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use WPCLI_DumpCommand\Exceptions\ZipFailedException;

/**
 * Helper class to create zip files
 */
final class ZipFile {
	
	/**
	 * Create a zip file from a directory
	 * 
	 * @throws ZipFailedException If the zip file can't be created
	 *
	 * @param string $filename The name of the zip file
	 * @param string $from The path of the directory to zip
	 * @param string $to The path where to save the zip file
	 *
	 * @return boolean True if the zip file has been created successfully
	 */
	public static function create( string $filename, string $from, string $to ): bool {
		$filename = sanitize_file_name( $filename );

		// Early check to avoid useless operations
		if ( ! file_exists( $from ) ) throw new ZipFailedException( sprintf( 'The source path "%s" doesn\'t exist.', $from ) );
		if ( ! file_exists( $to ) ) throw new ZipFailedException( sprintf( 'The destination path "%s" doesn\'t exist.', $to ) );
		if ( file_exists( $to . '/' . $filename ) ) throw new ZipFailedException( sprintf( 'File "%s" already exists. Please choose a different name or try later.', $filename ) );

		// Create the zip file
		$zip   = new ZipArchive();
		$files = self::get_files( $from );

		// Bail if there are no files to zip
		if ( empty( $files ) ) {
			throw new ZipFailedException( sprintf( 'There are no files to zip in the directory "%s".', $from ) );
		}

		// Bail if the zip file couldn't be created
		if ( $zip->open( $to . '/' . $filename, ZipArchive::CREATE ) !== true ) {
			throw new ZipFailedException( sprintf( 'The zip file "%s" couldn\'t be created.', $filename ) );
		}

		// Add all files to the zip file
		foreach ( $files as $file ) {
			$zip->addFile( $file, str_replace( $from . '/', '', $file ) );
		}

		// Close the zip file
		$zip->close();

		return true;
	}

	/**
	 * Get all files from a directory
	 *
	 * @param string $directory The directory to scan
	 *
	 * @return array The list of files
	 */
	private static function get_files( string $directory ): array {
		$files = [];

		$directory_iterator = new RecursiveDirectoryIterator( $directory, RecursiveDirectoryIterator::SKIP_DOTS );
		$iterator           = new RecursiveIteratorIterator( $directory_iterator, RecursiveIteratorIterator::SELF_FIRST );

		foreach ( $iterator as $file ) {
			if ( $file->isFile() ) {
				$files[] = $file->getPathname();
			}
		}

		return $files;
	}
}