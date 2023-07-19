<?php

namespace WPCLI_DumpCommand;

/**
 * Register here all things to do when the plugin is loaded
 */
final class PluginLoaded {

	/**
	 * Checker to see if the plugin has been initialized.
	 *
	 * @var bool
	 */
	private static bool $_initialized = false;

	public static function init(): void {
		if ( self::$_initialized ) {
			return;
		}

		self::$_initialized = true;
		self::boot();
	}

	/**
	 * Boot everything that needs to be booted.
	 *
	 * @return void
	 */
	private static function boot(): void {
		// Need WP-CLI to register the commands // @TODO Move this to a separate class
		// if ( ( defined( 'WP_CLI' ) && WP_CLI ) ) {
		// 	\WP_CLI::add_command( 'dump', Commands\WPCLI_Dump::class );
		// }
	}
}