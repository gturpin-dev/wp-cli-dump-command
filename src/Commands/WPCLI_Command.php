<?php

namespace WPCLI_DumpCommand\Commands;

/**
 * Class to be extended by all WP CLI commands
 * The command must be binded to the child class
 * This one will route the call to the register method
 * So the child class must implement the register method to be executed
 */
abstract class WPCLI_Command extends \WP_CLI_Command {

	/**
	 * The code called when the command is executed
	 * 
	 * @param array $args The list of arguments
	 * @param array $assoc_args The list of associative arguments
	 *
	 * @return void
	 */
	abstract protected function register( array $args, array $assoc_args ): void;

	/**
	 * Called automatically when the command is executed
	 * Route to the register method
	 * 
	 * @param array $args The list of arguments
	 * @param array $assoc_args The list of associative arguments
	 *
	 * @return void
	 */
	public function __invoke( array $args, array $assoc_args ): void {
		$this->register( $args, $assoc_args );
	}
}