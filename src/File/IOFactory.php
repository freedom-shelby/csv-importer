<?php

namespace Importer\File;

use Exception;
use Importer\File\Reader\IReader;

abstract class IOFactory
{
    private static array $readers = [
        // todo:: Implement more File types
        'Csv' => Reader\Csv::class,
    ];

    /**
     * @param $readerType
     * @return Reader\IReader
     * @throws Exception
     */
    public static function createReader($readerType): Reader\IReader
    {
        if (!isset(self::$readers[$readerType])) {
            throw new Exception("No reader found for type $readerType");
        }

        $className = self::$readers[$readerType];

        return new $className();
    }

    /**
     * @param $pFilename
     * @return IReader
     * @throws Exception
     */
    public static function load($pFilename)
    {
        $reader = self::createReaderForFile($pFilename);

        return $reader->load($pFilename);
    }

    /**
     * @param $filename
     * @return Reader\IReader
     * @throws Exception
     */
    public static function createReaderForFile($filename)
    {
        File::assertFile($filename);

        $guessedReader = self::getReaderTypeFromExtension($filename);
        if ($guessedReader !== null) {
            $reader = self::createReader($guessedReader);

            if (isset($reader) && $reader->canRead($filename)) {
                return $reader;
            }
        }

        throw new Exception('Unable to identify a reader for this file');
    }

    /**
     * @param $filename
     * @return string|null
     */
    private static function getReaderTypeFromExtension($filename)
    {
        $pathInfo = pathinfo($filename);

        if (!isset($pathInfo['extension'])) {
            return null;
        }

        return match (strtolower($pathInfo['extension'])) {
            // todo:: Implement more File types
            'csv' => 'Csv',
            default => null,
        };
    }
}
