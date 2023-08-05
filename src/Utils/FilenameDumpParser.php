<?php

namespace WPCLI_DumpCommand\Utils;

use DateTime;
use WPCLI_DumpCommand\Exceptions\BadDumpFilenameException;

/**
 * A parser for the filename of a dump.
 * Each instance of the class represent a single dump.
 */
final class FilenameDumpParser {

	private const ALLOWED_EXTENSIONS = [ 'sql', 'zip' ];

	/**
	 * The name of the dump without other filename stuff.
	 */
	private string $name;

	/**
	 * The date of the dump.
	 */
	private DateTime $date;

	/**
	 * The extension of the dump.
	 */
	private string $extension;

	/**
	 * @param string $filename The filename to parse.
	 */
	public function __construct(
		private string $filename,
	) {
		$this->check_filename();
		$this->parse();
	}

	/**
	 * Check if the filename is valid to parse
	 * The format must be {chosen filename}_{date Ymd}_{time his}.{extension}
	 * 
	 * @throws BadDumpFilenameException If the filename is not valid
	 *
	 * @return bool True if the filename is valid
	 */
	private function check_filename(): bool {
		$filename = $this->filename;

		// Check if the filename has the right extension ( could be sql or zip )
		$extension = pathinfo( $filename, PATHINFO_EXTENSION );
		if ( ! in_array( $extension, self::ALLOWED_EXTENSIONS ) ) {
			throw new BadDumpFilenameException( sprintf( 'The dump filename must have the right extension ( %s )', implode( 'or ', self::ALLOWED_EXTENSIONS ) ) );
		}

		// Check if the filename has the right format
		$filename_without_extension = pathinfo( $filename, PATHINFO_FILENAME );
		$filename_parts             = explode( '_', $filename_without_extension );

		if ( count( $filename_parts ) !== 3 ) {
			throw new BadDumpFilenameException( 'The dump filename must have the right format ( {chosen filename}_{date Ymd}_{time his}.{extension} )' );
		}

		// Check if the filename has the right date format
		$date = $filename_parts[1];
		if ( ! preg_match( '/^\d{8}$/', $date ) ) {
			throw new BadDumpFilenameException( 'The dump filename must have the right date format' );
		}

		// Check if the filename has the right time format
		$time = $filename_parts[2];
		if ( ! preg_match( '/^\d{6}$/', $time ) ) {
			throw new BadDumpFilenameException( 'The dump filename must have the right time format' );
		}

		return true;
	}

	/**
	 * Parse the filename and store the data in the class properties.
	 * The check_filename() method must be called before this method.
	 * So we can assume that the filename is valid.
	 *
	 * @return void
	 */
	private function parse(): void {
		$filename = $this->filename;

		// Get the extension
		$this->extension = pathinfo( $filename, PATHINFO_EXTENSION );

		// Get the name
		$filename_without_extension = pathinfo( $filename, PATHINFO_FILENAME );
		$filename_parts             = explode( '_', $filename_without_extension );
		$this->name                 = $filename_parts[0];

		// Get the date
		$date = $filename_parts[1];
		$this->date = DateTime::createFromFormat( 'Ymd', $date );

		// Get the time
		$time = $filename_parts[2];
		$this->date->setTime(
			(int) substr( $time, 0, 2 ),
			(int) substr( $time, 2, 2 ),
			(int) substr( $time, 4, 2 ),
		);
	}

	/**
	 * Getters and setters
	 */

	public function get_filename(): string {
		return $this->filename;
	}

	public function get_name(): string {
		return $this->name;
	}

	public function get_date(): DateTime {
		return $this->date;
	}

	public function get_extension(): string {
		return $this->extension;
	}

	public function get_download_url(): string {
		return WP_CONTENT_URL . '/dumps/' . $this->filename;
	}
}