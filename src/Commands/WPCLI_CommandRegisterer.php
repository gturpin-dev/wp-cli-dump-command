<?php

namespace WPCLI_DumpCommand\Commands;

use WP_CLI;
use WPCLI_DumpCommand\PHPAttributes\Action;
use WPCLI_DumpCommand\Commands\WPCLI_DumpExport;

/**
 * Register all wp-cli commands here
 */
final class WPCLI_CommandRegisterer {
	
	/**
	 * Register all commands
	 *
	 * @return void
	 */
	#[Action( 'cli_init' )]
	public function register(): void {
		// Bail if not in a WP_CLI context
		if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) return;

		WP_CLI::add_command( 'dump:export', WPCLI_DumpExport::class );
	}
}