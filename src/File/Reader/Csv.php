<?php

namespace Importer\File\Reader;

use Exception;
use Generator;

class Csv extends BaseReader
{
    const UTF8_BOM = "\xEF\xBB\xBF";
    const UTF8_BOM_LEN = 3;

    /**
     * Delimiter.
     *
     * @var string
     */
    private string $delimiter = ',';

    /**
     * Enclosure.
     *
     * @var string
     */
    private string $enclosure = '"';


    /**
     * The character that can escape the enclosure.
     *
     * @var string
     */
    private string $escapeCharacter = '\\';

    /**
     * Move filepointer past any BOM marker.
     */
    protected function skipBOM(): void
    {
        rewind($this->fileHandle);

        if (fgets($this->fileHandle, self::UTF8_BOM_LEN + 1) !== self::UTF8_BOM) {
            rewind($this->fileHandle);
        }
    }

    /**
     * @param string $pFilename
     * @return Csv
     * @throws Exception
     */
    public function load(string $pFilename): static
    {
        $this->openFile($pFilename);

        $this->skipBOM();

        return $this;
    }

    /**
     * @return Generator
     */
    public function readLineByLine(): Generator
    {
        $fileHandle = $this->fileHandle;

        while (($rowData = fgetcsv($fileHandle, 0, $this->delimiter, $this->enclosure, $this->escapeCharacter)) !== false) {
            yield $rowData;
        }

        fclose($fileHandle);

        return;
    }

    /**
     * Can the current IReader read the file?
     *
     * @param string $pFilename
     *
     * @return bool
     * @throws Exception
     */
    public function canRead(string $pFilename): bool
    {
        try {
            $this->openFile($pFilename);
        } catch (Exception) {
            return false;
        }

        fclose($this->fileHandle);

        $extension = strtolower(pathinfo($pFilename, PATHINFO_EXTENSION));
        if (in_array($extension, ['csv', 'tsv'])) {
            return true;
        }

        return false;
    }
}
