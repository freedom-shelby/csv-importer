<?php

namespace Importer;

class DatabaseQueryMaker
{
    /**
     * @param $tableName
     * @param $colNames
     * @param $dataToInsert
     * @return string
     */
    public static function makeBulkInsert($tableName, $colNames, $dataToInsert): string
    {
        // setup the ON DUPLICATE column names
        $updateCols = [];

        foreach ($colNames as $name) {
            $updateCols[] = $name . " = VALUES($name)";
        }

        $onDup = implode(', ', $updateCols);

        // setup the placeholders - a fancy way to make the long "(?, ?, ?)..." string
        $rowPlaces = '(' . implode(', ', array_fill(0, count($colNames), '?')) . ')';
        $allPlaces = implode(', ', array_fill(0, count($dataToInsert), $rowPlaces));

        $sql = "INSERT INTO {$tableName} (" . implode(', ', $colNames) .
            ") VALUES " . $allPlaces . " ON DUPLICATE KEY UPDATE {$onDup}";

        return $sql;
    }
}