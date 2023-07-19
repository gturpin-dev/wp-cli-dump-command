<?php

namespace WPCLI_DumpCommand;

use WPCLI_DumpCommand\Internal\HookRegisterer;
use WPCLI_DumpCommand\PHPAttributes\HookInterface;

/**
 * This class is the entry point of the plugin.
 * It is responsible for loading the plugin's dependencies and store the plugin's information.
 */
final class Plugin {

	/**
	 * The plugin's instance.
	 *
	 * @var self|null
	 */
	private static ?self $_instance = null;

	/**
	 * The plugin's main file.
	 */
	private string $main_file;

	/**
	 * The plugin's slug.
	 */
	private string $slug;

	/**
	 * The plugin's path (the directory where the plugin's functions file is located).
	 */
	private string $path;

	/**
	 * The plugin's URL.
	 */
	private string $url;

	/**
	 * The plugin's version.
	 */
	private string $version;

	/**
	 * The plugin's data
	 * Note that some important informations are also store directly in the class properties.
	 * @see https://developer.wordpress.org/reference/functions/get_plugin_data/ for more information.
	 */
	private array $plugin_data;

	/**
	 * Initialize the plugin.
	 * Can be called only once.
	 * 
	 * @param string $plugin_main_file The plugin's main file.
	 *
	 * @return void
	 */
	public static function init( string $plugin_main_file ): void {
		if ( ! is_null( self::$_instance ) ) {
			throw new \Exception( 'The plugin has already been initialized.' );
		};

		self::$_instance = new self( $plugin_main_file );
	}

	/**
	 * The plugin's constructor.
	 * Called only once by the init() method.
	 * Store the plugin's data and load the plugin's features.
	 * @psalm-suppress MissingFile
	 * 
	 * @param string $plugin_main_file The plugin's main file.
	 */
	private function __construct( string $plugin_main_file ) {
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$this->main_file   = $plugin_main_file;
		$this->plugin_data = get_plugin_data( $plugin_main_file );
		$this->path        = plugin_dir_path( $plugin_main_file );
		$this->url         = plugin_dir_url( $plugin_main_file );
		$this->slug        = basename( $this->path );
		$this->version     = $this->plugin_data['Version'];
		$this->boot();
	}

	/**
	 * Boot the plugin.
	 *
	 * @return void
	 */
	private function boot(): void {
		// Bind the plugin activation features
		register_activation_hook( $this->main_file, [ PluginActivation::class, 'init' ] );

		// Bind the plugin deactivation features
		register_deactivation_hook( $this->main_file, [ PluginDeactivation::class, 'init' ] );

		// Bind the plugin features that need to be loaded each time
		add_action( 'plugins_loaded', [ PluginLoaded::class, 'init' ] );

		$hooked_class = require_once $this->get_path() . '/config/hooks.php';
		$this->register_hooked_classes( $hooked_class );
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

	// Prevent the instance from being cloned.
	private function __clone() {}

	// Prevent from being unserialized.
	public function __wakeup() {}

	/**
	 * Get the plugin's instance.
	 *
	 * @return self The plugin's instance.
	 */
	public static function get_instance(): self {
		var_dump( self::$_instance );
		die();
		if ( is_null( self::$_instance ) ) {
			throw new \Exception( 'The plugin has not been initialized yet.' );
		}

		return self::$_instance;
	}

	/**
	 * Get the plugin's slug.
	 *
	 * @return string The plugin's slug.
	 */
	public function get_slug(): string {
		return $this->slug;
	}

	/**
	 * Get the plugin's data.
	 *
	 * @return array The plugin's data.
	 */
	public function get_plugin_data(): array {
		return $this->plugin_data;
	}

	/**
	 * Get the plugin's path.
	 *
	 * @return string The plugin's path.
	 */
	public function get_path(): string {
		return $this->path;
	}

	/**
	 * Get the plugin's URL.
	 *
	 * @return string The plugin's URL.
	 */
	public function get_url(): string {
		return $this->url;
	}

	/**
	 * Get the plugin's version.
	 *
	 * @return string The plugin's version.
	 */
	public function get_version(): string {
		return $this->version;
	}
}
