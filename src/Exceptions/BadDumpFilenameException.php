<?php

namespace WPCLI_DumpCommand\Exceptions;

use Exception;

/**
 * Exception thrown when the filename of a dump is not valid.
 */
final class BadDumpFilenameException extends Exception {}