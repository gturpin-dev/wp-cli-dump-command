<?php

namespace WPCLI_DumpCommand\Views;

use WPCLI_DumpCommand\Export\ExportFile;
use WPCLI_DumpCommand\Utils\FilenameDumpParser;
use WPCLI_DumpCommand\Admin\OptionPages\CustomDumps;
use WPCLI_DumpCommand\Utils\Markup\HTML_Select;

// require WP_List_Table
if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * This custom list table displays the dumps in the admin area.
 */
final class Dumps_List_Table extends \WP_List_Table {

	private const ITEMS_PER_PAGE = 10;
	
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
	 * Process the custom actions if they are correctly triggered.
	 * 
	 * @param string $action  The action to process.
	 * @param array  $elements  The elements to process.
	 * @param string $nonce  The nonce to verify.
	 *
	 * @return void
	 */
	public static function process_custom_actions( string $action, array $elements, ?string $nonce ): void {
		// Bail if no action
		if ( is_null( $action ) || $action == -1 ) return;

		match ( $action ) {
			'download' => self::process_download_action( $elements, $nonce ),
			'delete'   => self::process_delete_action( $elements, $nonce ),
			default    => null,
		};
	}

	/**
	 * Process the delete action
	 * 
	 * @param array  $elements The elements to process.
	 * @param string $nonce    The nonce to verify.
	 *
	 * @return void
	 */
	private static function process_delete_action( array $elements, ?string $nonce ): void {
		// Bail only if the nonce is needed ( not null ) and it's not verified
		if ( ! is_null( $nonce ) && ! wp_verify_nonce( $nonce, 'delete_dump' ) ) return;

		array_walk( $elements, function ( $filename ) {
			$filename = sanitize_text_field( $filename );
			ExportFile::delete( $filename );
		} );

		// Redirect to the option page.
		wp_safe_redirect( admin_url( 'admin.php?page=' . CustomDumps::PAGE_SLUG ) );
	}

	/**
	 * Process the download action
	 * 
	 * @param array  $elements The elements to process.
	 * @param string $nonce    The nonce to verify.
	 *
	 * @return void
	 */
	private static function process_download_action( array $elements, ?string $nonce ): void {
		// Bail only if the nonce is needed ( not null ) and it's not verified
		if ( ! is_null( $nonce ) && ! wp_verify_nonce( $nonce, 'download_dump' ) ) return;

		array_walk( $elements, function ( $filename ) {
			$filename = sanitize_text_field( $filename );
			ExportFile::download( $filename );
		} );

		// Redirect to the option page.
		wp_safe_redirect( admin_url( 'admin.php?page=' . CustomDumps::PAGE_SLUG ) );
	}

	/**
	 * Displays extra controls between bulk actions and pagination.
	 *
	 * @param string $which The location of the extra table nav markup: 'top' or 'bottom'.
	 *
	 * @return void
	 */
	protected function extra_tablenav( $which ): void {
		// Bail if not top
		if ( $which !== 'top' ) return;

		
		// Fill the select with the months and years
		$filter_by_month = $_REQUEST['filter_by_month'] ?? 'all';
		$months          = [];

		// Bail if the dump directory does not exist.
		if ( ! is_dir( ExportFile::EXPORT_PATH ) ) return;

		$files = scandir( ExportFile::EXPORT_PATH, SCANDIR_SORT_DESCENDING );
		$files = array_diff( $files, [ '.', '..' ] );
		$dates = array_map( fn ( $filename ) => ( new FilenameDumpParser( $filename ) )->get_date(), $files );

		array_walk( $dates, function ( $datetime ) use ( &$months ) {
			$month    = $datetime->format( 'mY' );
			$label    = $datetime->format( 'F Y' );

			$months[ $month ] = $label;
		} );
		
		$select = new HTML_Select( [
			'all' => 'All',
			...$months,
		] );
		$select->set_selected( $filter_by_month );

		echo '<div class="alignleft actions">';
		echo $select->build( 'filter_by_month' );
		echo get_submit_button( 'Filter', 'secondary', null, false );
		echo '</div>';
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

	/**
	 * Sorting function for usort().
	 * Manage the sorting of the table weither for order or orderby and DateTime.
	 *
	 * @param mixed $a The first element to compare.
	 * @param mixed $b The second element to compare.
	 *
	 * @return int The result of the comparison.
	 */
    private function usort_reorder( $a, $b ): int {
		// If no sort, default to date_added
		$orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'date_added';

		// If no order, default to asc
		$order = ( ! empty( $_GET['order'] ) ) ? $_GET['order'] : 'desc';

		// If the orderby is 'date_added', convert the date to a timestamp.
		if ( $orderby === 'date_added' ) {
			$a_date_added = isset( $a['date_added'] ) ? $a['date_added']->getTimestamp() : 0;
			$b_date_added = isset( $b['date_added'] ) ? $b['date_added']->getTimestamp() : 0;

			return ( $order === 'asc' ) ? $a_date_added - $b_date_added : $b_date_added - $a_date_added;
		}

		// Basic comparison
        $result = strcmp( $a[ $orderby ], $b[ $orderby ] );

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
		// Set up column headers, sortable columns, hidden columns etc.
		$this->_column_headers = [ $this->get_columns(), [], $this->get_sortable_columns() ];

		// Check for search query.
		$search = isset( $_POST['s'] ) ? sanitize_text_field( $_POST['s'] ) : '';

		// Retrieve data
		$items          = $this->get_items( $search );
		$posts_per_page = self::ITEMS_PER_PAGE;
		$current_page   = $this->get_pagenum();
		$total_items    = count( $items );
		$offset         = ( $current_page - 1 ) * $posts_per_page;          // Calculate the offset for pagination.
		$items          = array_slice( $items, $offset, $posts_per_page );  // Set the items for display.
		$this->items    = $items;

		// Set the pagination arguments.
		$this->set_pagination_args( [
			'total_items' => $total_items,
			'per_page'    => $posts_per_page,
			'total_pages' => ceil( $total_items / $posts_per_page ),
		] );
	}

	/**
	 * Retrieve the items
	 * 
	 * @param string $search Search query.
	 *
	 * @return array $items Data for display in the table.
	 */
	private function get_items( string $search = '' ): array {
		$dump_dir_path = ExportFile::EXPORT_PATH;

		// Bail if the dump directory does not exist.
		if ( ! is_dir( $dump_dir_path ) ) return [];

		$files = scandir( $dump_dir_path, SCANDIR_SORT_DESCENDING );
		$files = array_diff( $files, [ '.', '..' ] );

		// Filter the files by the search query.
		if ( ! empty( $search ) ) {
			$files = array_filter( $files, function( $filename ) use ( $search ) {
				return str_contains( $filename, $search );
			} );
		}

		// Filter the files by the month filter if set.
		$filter_by_month = $_REQUEST['filter_by_month'] ?? 'all';
		if ( ! empty( $filter_by_month ) && $filter_by_month !== 'all' ) {
			$files = array_filter( $files, function( $filename ) use ( $filter_by_month ) {
				$parsed_file = new FilenameDumpParser( $filename );
				
				return $parsed_file->get_date()->format( 'mY' ) === $filter_by_month;
			} );
		}

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

	/**
	 * Function to show the bulk actions.
	 *
	 * @return void
	 */
    public function get_bulk_actions() {
		$actions = [
			// 'download' => __( 'Download', 'wp-cli-dump-command' ), // @TODO Maybe to remove
			'delete'   => __( 'Delete', 'wp-cli-dump-command' ),
		];

		return $actions;
    }

	/**
	 * WP call "views" the filters on the top of the table above the bulk actions.
	 *
	 * @return array The views for the table
	 */
	protected function get_views() {
		$links = [
			'all',
			'plugins',
			'themes',
			'uploads',
			'database'
		];
		$base_url = admin_url( 'admin.php?page=' . CustomDumps::PAGE_SLUG );
		$links    = array_combine( $links, $links );
		$links    = array_map( function( $link ) use ( $base_url ) {
			$url   = add_query_arg( 'dump_type', $link, $base_url );
			$class = ( isset( $_GET['dump_type'] ) && $_GET['dump_type'] === $link ) ? 'current' : '';
			$label = ucfirst( $link );

			return sprintf( '<a href="%s" class="%s">%s</a>', esc_url( $url ), $class, $label );
		}, $links );
		
		return $links;
	}

	/** Text displayed when no item data is available */
	public function no_items() {
		_e( 'No dumps available.', 'wp-cli-dump-command' );
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

		// Creates nonces for the actions.
		$delete_nonce   = wp_create_nonce( 'delete_dump' );
		$download_nonce = wp_create_nonce( 'download_dump' );

		$base_url = admin_url( 'admin.php?page=' . CustomDumps::PAGE_SLUG );
		$actions  = [
			'download' => sprintf(
				'<a href="%1$s">%2$s</a>',
				add_query_arg( [ 
					'action'   => 'download',
					'element' => $item['filename'] ?? '',
					'nonce'    => $download_nonce,
				], $base_url ),
				__( 'Download', 'wp-cli-dump-command' )
			),
			'delete'   => sprintf(
				'<a href="%1$s">%2$s</a>',
				add_query_arg( [ 
					'action'   => 'delete',
					'element' => $item['filename'] ?? '',
					'nonce'    => $delete_nonce,
				], $base_url ),
				__( 'Delete', 'wp-cli-dump-command' )
			),
		];
		
		return sprintf( '%1$s %2$s', $item['filename'], $this->row_actions( $actions ) );
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

		$base_url = admin_url( 'admin.php?page=' . CustomDumps::PAGE_SLUG );
		$actions  = [
			'download' => sprintf(
				'<a href="%1$s" class="button button-primary">%2$s</a>',
				add_query_arg( [ 
					'action'   => 'download',
					'element' => $item['filename'] ?? '',
					'nonce'    => $download_nonce,
				], $base_url ),
				__( 'Download', 'wp-cli-dump-command' )
			),
			'delete'   => sprintf(
				'<a href="%1$s" class="button button-error">%2$s</a>',
				add_query_arg( [ 
					'action'   => 'delete',
					'element' => $item['filename'] ?? '',
					'nonce'    => $delete_nonce,
				], $base_url ),
				__( 'Delete', 'wp-cli-dump-command' )
			),
		];

		return implode( ' ', $actions );
	}
}