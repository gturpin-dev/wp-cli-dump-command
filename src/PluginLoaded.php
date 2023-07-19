<?php

namespace WPCLI_DumpCommand;

use WPCLI_DumpCommand\PHPAttributes\HookInterface;

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
		// Register the hooked classes with PHP attributes
		$hooked_class = require_once Plugin::get_instance()->get_path() . '/config/hooks.php';
		self::register_hooked_classes( $hooked_class );
	}

	/**
	 * Bind all hooks defined in the hooked classes config.
	 * 
	 * @param array $hooked_classes List of classes to register
	 *
	 * @return void
	 */
	private static function register_hooked_classes( array $hooked_classes ): void {
		$instances = [];

		foreach ( $hooked_classes as $hooked_class ) {
			if ( array_key_exists( $hooked_class, $instances ) ) continue;

			$reflection_class = new \ReflectionClass( $hooked_class );

			foreach ( $reflection_class->getMethods() as $method ) {
				$attributes = $method->getAttributes( HookInterface::class, \ReflectionAttribute::IS_INSTANCEOF );

				foreach ( $attributes as $attribute ) {
					// instanciate the hooked class
					$hooked_class_instance      = $instances[ $hooked_class ] ?? new $hooked_class();
					$instances[ $hooked_class ] = $hooked_class_instance;

					// instanciate the hook_attribute class
					$hook_attribute_class = $attribute->newInstance();
					$hook_attribute_class->register( [ $hooked_class_instance, $method->getName() ] );
				}
			}
		}
	}
}