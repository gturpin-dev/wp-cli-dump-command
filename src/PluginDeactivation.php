<?php

namespace WPCLI_DumpCommand;

/**
 * Register here all things to do on plugin deactivation.
 */
final class PluginDeactivation {

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
		// Add stuff on plugin deactivation here
	}
}