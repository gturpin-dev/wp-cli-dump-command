<?php

namespace WPCLI_DumpCommand;

/**
 * Register here all things to do on plugin uninstall.
 */
final class PluginUninstall {

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
		// If the constant is not defined, then the plugin is not being uninstalled by WP
		if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;
		
		// Add stuff on plugin uninstall here
	}
}