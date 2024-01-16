<?php

namespace Importer\File;

use InvalidArgumentException;

class File
{
    /**
     * Assert that given path is an existing file and is readable, otherwise throw exception.
     *
     * @param string $filename
     */
    public static function assertFile($filename): void
    {
        if (!is_file($filename)) {
            throw new InvalidArgumentException('File "' . $filename . '" does not exist.');
        }

        if (!is_readable($filename)) {
            throw new InvalidArgumentException('Could not open "' . $filename . '" for reading.');
        }
    }
}
