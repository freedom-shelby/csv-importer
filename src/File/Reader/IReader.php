<?php

namespace Importer\File\Reader;

use Generator;

interface IReader
{
    /**
     * @param string $pFilename
     * @return bool
     */
    public function canRead(string $pFilename): bool;

    /**
     * @param string $pFilename
     * @return IReader
     */
    public function load(string $pFilename): static;

    /**
     * @return Generator
     */
    public function readLineByLine(): Generator;
}
