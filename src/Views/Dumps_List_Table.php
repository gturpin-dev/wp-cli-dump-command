<?php

namespace WPCLI_DumpCommand\Views;

use WPCLI_DumpCommand\Utils\FilenameDumpParser;

// require WP_List_Table
if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * This custom list table displays the dumps in the admin area.
 */
final class Dumps_List_Table extends \WP_List_Table {
	
	/**
	 * Initialize the list table.
	 */
	public function __construct() {
		parent::__construct( [
			'singular' => __( 'dump', 'wp-cli-dump-command' ),
			'plural'   => __( 'dumps', 'wp-cli-dump-command' ),
			'ajax'     => false,
		] );
	}

	/**
	 * Define the columns to be displayed in the table.
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = [
			'filename'   => __( 'File Name', 'wp-cli-dump-command' ),
			'date_added' => __( 'Date Added', 'wp-cli-dump-command' ),
			'actions'    => __( 'Actions', 'wp-cli-dump-command' ),
		];

		return $columns;
	}

	/**
	 * Define the columns which are sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return [];
		// $sortable_columns = [
		// 	'id'         => ['id', true],
		// 	'file_name'  => ['file_name', false],
		// 	'date_added' => ['date_added', false],
		// ];

		// return $sortable_columns;
	}

	/**
	 * Retrieve the data for the table.
	 * Populate the table rows, sorting, pagination, and other data.
	 *
	 * @return array
	 */
	public function prepare_items() {
		// Set up column headers, sortable columns, and bulk actions.
		$this->_column_headers = [ $this->get_columns(), [], $this->get_sortable_columns() ];

		// @TODO Process any bulk actions if triggered.

		$items          = $this->get_items();
		$total_items    = count( $items );
		$current_page   = $this->get_pagenum();
		$posts_per_page = 10;
		$offset         = ( $current_page - 1 ) * $posts_per_page;          // Calculate the offset for pagination.
		$items          = array_slice( $items, $offset, $posts_per_page );  // Set the items for display.

		// Set the items for display.
		$this->items = $items;

		// Set the pagination arguments.
		$this->set_pagination_args( [
			'total_items' => $total_items,
			'per_page'    => $posts_per_page,
		] );
	}

	/**
	 * Retrieve the items
	 *
	 * @return array $items Data for display in the table.
	 */
	private function get_items(): array {
		// @TODO getting this path from other source
		$dump_dir_path = WP_CONTENT_DIR . '/dumps';

		// Bail if the dump directory does not exist.
		if ( ! is_dir( $dump_dir_path ) ) return [];

		$files = scandir( $dump_dir_path, SCANDIR_SORT_DESCENDING );
		$files = array_diff( $files, [ '.', '..' ] );

		// Map the files to an array of items.
		$items = array_map( function( $filename ) {
			$parsed_file = new FilenameDumpParser( $filename );
			return [
				'filename'   => $parsed_file->get_filename(),
				'date_added' => $parsed_file->get_date(),
				'url'        => $parsed_file->get_download_url(),
			];
		}, $files );

		return $items;
	}

	/**
	 * Render the File Name column.
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	public function column_filename( $item ) {
		return $item['filename'] ?? '';
	}

	/**
	 * Render the Date Added column.
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	public function column_date_added( $item ) {

		// Bail if the date is not a DateTime object.
		if ( ! $item['date_added'] instanceof \DateTime ) return '';

		return $item['date_added']->format( 'Y-m-d H:i:s' );
	}

	/**
	 * Render the actions column.
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	public function column_actions( $item ) {

		// Make a button to download the file with his url.
		$actions = sprintf(
			'<a href="%1$s" class="button button-primary">%2$s</a>',
			$item['url'] ?? '',
			__( 'Download', 'wp-cli-dump-command' )
		);
		
		return $actions;
	}
}