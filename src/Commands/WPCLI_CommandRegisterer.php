<?php

namespace WPCLI_DumpCommand\Commands;

use WP_CLI;
use WPCLI_DumpCommand\PHPAttributes\Action;
use WPCLI_DumpCommand\Commands\WPCLI_DumpExportThemes;
use WPCLI_DumpCommand\Commands\WPCLI_DumpExportDatabase;

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

		WP_CLI::add_command( 'dump:export database', WPCLI_DumpExportDatabase::class );
		WP_CLI::add_command( 'dump:export themes', WPCLI_DumpExportThemes::class );
		WP_CLI::add_command( 'dump:export plugins', WPCLI_DumpExportPlugins::class );
		WP_CLI::add_command( 'dump:export uploads', WPCLI_DumpExportUploads::class );
	}
}