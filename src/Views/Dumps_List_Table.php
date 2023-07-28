<?php

namespace WPCLI_DumpCommand\Views;

use WPCLI_DumpCommand\Export\ExportFile;
use WPCLI_DumpCommand\Utils\FilenameDumpParser;
use WPCLI_DumpCommand\Admin\OptionPages\CustomDumps;

// require WP_List_Table
if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * This custom list table displays the dumps in the admin area.
 */
final class Dumps_List_Table extends \WP_List_Table {

	private const ITEMS_PER_PAGE = 15;
	
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
	 * Display the table.
	 *
	 * @return void
	 */
	public function display() {
		parent::display();
	}

	/**
	 * Check for custom actions and process them.
	 *
	 * @return void
	 */
	private function process_custom_actions() {
		$this->maybe_process_delete_action();
		$this->maybe_process_download_action();
	}

	/**
	 * Check if the download action was triggered and process it.
	 *
	 * @return void
	 */
	private function maybe_process_delete_action() {
		// Bail if the action is not triggered.
		if ( ! isset( $_GET['action'] ) ) return;
		if ( $_GET['action'] !== 'delete' ) return;

		// Bail if the nonce is not valid.
		if ( ! isset( $_GET['nonce'] ) ) return;
		if ( ! wp_verify_nonce( $_GET['nonce'], 'delete_dump' ) ) return;

		// Bail if the dump ID is not valid.
		if ( ! isset( $_GET['filename'] ) ) return;

		$filename = sanitize_text_field( $_GET['filename'] );
		
		// Delete the dump.
		ExportFile::delete( $filename );

		// Redirect to the option page.
		wp_safe_redirect( admin_url( 'admin.php?page=' . CustomDumps::PAGE_SLUG ) );
	}

	/**
	 * Define the columns to be displayed in the table.
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = [
			'cb'         => '<input type="checkbox" />',
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
		$sortable_columns = [
			'filename'   => [ 'filename', false ],
			'date_added' => [ 'date_added', false ],
		];

		return $sortable_columns;
	}

	/**
	 * Sorting function for usort().
	 */
    private function usort_reorder( $a, $b ) {
        // If no sort, default to user_login
        $orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'date_added';

        // If no order, default to asc
        $order = ( ! empty( $_GET['order'] ) ) ? $_GET['order'] : 'asc';


		// If the orderby is 'date_added', convert the date to a timestamp.
		if ( $orderby === 'date_added' ) {
			$a = $a['date_added'] ?? '';
			$b = $b['date_added'] ?? '';
			$a = $a->getTimestamp();
			$b = $b->getTimestamp();

			return ( $order === 'asc' ) ? $a - $b : $b - $a;
		}

		// Basic comparison
        $result = strcmp( $a[$orderby], $b[$orderby] );

        // Send final sort direction to usort
        return ( $order === 'asc' ) ? $result : -$result;
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

		// Process any individual custom actions.
		$this->process_custom_actions();

		$items          = $this->get_items();
		$total_items    = count( $items );
		$current_page   = $this->get_pagenum();
		$posts_per_page = self::ITEMS_PER_PAGE;
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
		$dump_dir_path = ExportFile::EXPORT_PATH;

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

		usort( $items, [ $this, 'usort_reorder' ] );

		return $items;
	}

	/** Text displayed when no item data is available */
	public function no_items() {
		_e( 'No items avaliable.', 'wp-cli-dump-command' );
	}

	/**
	 * Render the checkbox column.
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_cb( $item ) {
        return sprintf( '<input type="checkbox" name="element[]" value="%s" />', $item['filename'] );
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

		// Creates nonces for the actions.
		$delete_nonce   = wp_create_nonce( 'delete_dump' );
		$download_nonce = wp_create_nonce( 'download_dump' );

		$actions = [
			'delete'   => sprintf(
				'<a href="%1$s" class="button button-error">%2$s</a>',
				add_query_arg( [ 
					'action'   => 'delete',
					'filename' => $item['filename'] ?? '',
					'nonce'    => $delete_nonce,
				],
				admin_url( 'admin.php?page=' . CustomDumps::PAGE_SLUG ) ),
				__( 'Delete', 'wp-cli-dump-command' )
			),
			'download' => sprintf(
				'<a href="%1$s" class="button button-primary">%2$s</a>',
				add_query_arg( [ 
					'action'   => 'download',
					'filename' => $item['filename'] ?? '',
					'nonce'    => $download_nonce,
				],
				admin_url( 'admin.php?page=' . CustomDumps::PAGE_SLUG ) ),
				__( 'Download', 'wp-cli-dump-command' )
			),
		];

		return implode( ' ', $actions );
	}
}