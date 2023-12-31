<?php

namespace WPCLI_DumpCommand\Admin\OptionPages;

use WPCLI_DumpCommand\PHPAttributes\Action;
use WPCLI_DumpCommand\Views\Dumps_List_Table;

/**
 * Define an option page to display the dumps in the admin area.
 */
final class CustomDumps {
	
	public const PAGE_SLUG = 'wp-cli-dump-command';

	/**
	 * Handle the actions in the option page
	 * They can be bulk actions or single actions
	 * Then can be GET or POST
	 *
	 * @return void
	 */
	#[Action( 'admin_init' )]
	public function handle_actions(): void {
		$action   = $_REQUEST['action'] ?? $_REQUEST['action2'] ?? '';
		$elements = isset( $_REQUEST['element'] ) ? (array) $_REQUEST['element'] : [];
		$nonce    = $_REQUEST['nonce'] ?? null; // Null means no nonce needed

		Dumps_List_Table::process_custom_actions( $action, $elements, $nonce );
	}
	
	/**
	 * Register the option page
	 *
	 * @return void
	 */
	#[Action( 'admin_menu' )]
	public function register(): void {
		add_menu_page(
			__( 'Custom Dumps', 'wp-cli-dump-command' ),
			__( 'Custom Dumps', 'wp-cli-dump-command' ),
			'manage_options',
			self::PAGE_SLUG,
			[ $this, 'render' ],
			'dashicons-database-export',
			85
		);
	}

	/**
	 * Render the option page markup
	 *
	 * @return void
	 */
	public function render(): void {
		
		echo '<div class="wrap">';
		echo '<form method="POST">';
		echo '<h1>' . esc_html( get_admin_page_title() ) . '</h1>';
		
		$list_table = new Dumps_List_Table();
		$list_table->prepare_items();
		$list_table->views();
		$list_table->search_box( 'search', 'search_id' );
		$list_table->display();

		echo '</form>';
		echo '</div>';
	}

	/**
	 * Adding styles to the option page in the admin area
	 *
	 * @return void
	 */
	#[Action( 'admin_head' )]
	public function admin_styles() {
		?>
		<style>
			.wp-core-ui .button-error {
				background-color: #dc3232 !important;
				border-color: #dc3232 !important;
				color: #fff !important;
			}
		</style>
		<?php
	}
}