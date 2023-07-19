<?php

use WPCLI_DumpCommand\Admin\OptionPages\CustomDumps;
use WPCLI_DumpCommand\Commands\WPCLI_CommandRegisterer;

/**
 * List of hooks classes to register
 */
return [
	CustomDumps::class,
	WPCLI_CommandRegisterer::class,
];