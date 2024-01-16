<?php

namespace Importer\File\Reader;

use Exception;
use Importer\File\File;

abstract class BaseReader implements IReader
{
    /**
     * @var resource|false
     */
    protected $fileHandle;

    /**
     * @param $pFilename
     * @throws Exception
     */
    protected function openFile($pFilename): void
    {
        if ($pFilename) {
            File::assertFile($pFilename);

            // Open file
            $fileHandle = fopen($pFilename, 'rb');
        } else {
            $fileHandle = false;
        }

        if ($fileHandle !== false) {
            $this->fileHandle = $fileHandle;
        } else {
            throw new Exception('Could not open file ' . $pFilename . ' for reading.');
        }
    }
}
