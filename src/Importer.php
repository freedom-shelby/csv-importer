<?php

namespace Importer;

use Exception;
use Importer\File\IOFactory;
use Importer\File\Reader\IReader;
use PDO;
use Ramsey\Uuid\Uuid;

/**
 * Class Importer
 */
class Importer
{
    protected PDO $dbConnection;
    protected array $columnsPositions = [];
    protected IReader $sheet;
    protected int $insertIteration = 0;
    protected array $dataToInsertForMerchants = [];
    protected array $dataToInsertForBatches = [];
    protected array $dataToInsertForTransactions = [];
    protected array $dataAsArrayForMerchants = [];
    protected array $dataAsArrayForBatches = [];
    protected array $dataAsArrayForTransactions = [];

    /**
     * @var Result
     */
    protected Result $result;

    /**
     * For best performance need realise Bulk insert (but memory usage has increase)
     */
    const BULK_INSERT_LIMIT = 1024;


    /**
     * Importer constructor.
     * @param PDO $databaseConnectionFactory
     */
    public function __construct(PDO $databaseConnectionFactory)
    {
        $this->dbConnection = $databaseConnectionFactory;
        $this->result = new Result();
    }

    /**
     * Imports a given report
     *
     * @param string $filename Full path to the report
     * @param string[] $mapping Report mapping
     *
     * @return Result Result of the import process
     * @throws Exception
     */
    public function process(string $filename, array $mapping): Result
    {
        $this->sheet = IOFactory::load($filename);

        $this->resolveColumnPositions($this->sheet->readLineByLine()->current(), $mapping);

        $this->bulkInsert();

        return $this->result;
    }

    protected function bulkInsert()
    {
        foreach ($this->sheet->readLineByLine() as $item) {
            try {
                $this->insertIteration++;

                $merchantPrimaryKey = $item[$this->columnsPositions[Report::MERCHANT_ID]];
                $batchPrimaryKey = $item[$this->columnsPositions[Report::MERCHANT_ID]] . $item[$this->columnsPositions[Report::BATCH_DATE]] . $item[$this->columnsPositions[Report::BATCH_REF_NUM]];
                $batchUuid = (string)Uuid::uuid5(Uuid::NIL, $batchPrimaryKey);

                if (!isset($this->dataToInsertForMerchants[$merchantPrimaryKey])) {
                    $this->dataToInsertForMerchants[$merchantPrimaryKey] = $this->getMerchantDataFromArray($item);

                    foreach ($this->dataToInsertForMerchants[$merchantPrimaryKey] as $dataToInsertForMerchant) {
                        $this->dataAsArrayForMerchants[] = $dataToInsertForMerchant;
                    }
                }

                if (!isset($this->dataToInsertForBatches[$batchUuid])) {
                    $this->dataToInsertForBatches[$batchUuid] = $this->getBatchDataFromArray($item, $batchUuid);

                    foreach ($this->dataToInsertForBatches[$batchUuid] as $dataToInsertForBatch) {
                        $this->dataAsArrayForBatches[] = $dataToInsertForBatch;
                    }
                }

                $this->dataToInsertForTransactions[$this->insertIteration] = $this->getTransactionDataFromArray($item, $batchUuid);

                foreach ($this->dataToInsertForTransactions[$this->insertIteration] as $dataToInsertForTransaction) {
                    $this->dataAsArrayForTransactions[] = $dataToInsertForTransaction;
                }

                if ($this->insertIteration >= static::BULK_INSERT_LIMIT) {
                    $this->save();
                }
            } catch (Exception) {
                $this->dbConnection->rollback();

                $this->resetIteration();

                // todo:: save log

                continue;
            }
        }

        if ($this->insertIteration > 0) {
            $this->save();
        }
    }

    protected function save()
    {
        $this->dbConnection->beginTransaction();

        $sql = DatabaseQueryMaker::makeBulkInsert('merchants', Report::MERCHANT_COL_NAMES, $this->dataToInsertForMerchants);
        $stmt = $this->dbConnection->prepare($sql);
        $stmt->execute($this->dataAsArrayForMerchants);

        $this->result->addMerchantCount($stmt->rowCount());

        $sql = DatabaseQueryMaker::makeBulkInsert('batches', Report::BATCH_COL_NAMES, $this->dataToInsertForBatches);
        $stmt = $this->dbConnection->prepare($sql);
        $stmt->execute($this->dataAsArrayForBatches);

        $this->result->addBatchCount($stmt->rowCount());

        $sql = DatabaseQueryMaker::makeBulkInsert('transactions', Report::TRANSACTION_COL_NAMES, $this->dataToInsertForTransactions);
        $stmt = $this->dbConnection->prepare($sql);
        $stmt->execute($this->dataAsArrayForTransactions);

        $this->dbConnection->commit();

        $this->result->addTransactionCount($stmt->rowCount());

        $this->resetIteration();
    }

    protected function resetIteration()
    {
        $this->insertIteration = 0;
        $this->dataToInsertForMerchants = [];
        $this->dataToInsertForBatches = [];
        $this->dataToInsertForTransactions = [];
        $this->dataAsArrayForMerchants = [];
        $this->dataAsArrayForBatches = [];
        $this->dataAsArrayForTransactions = [];
    }

    protected function getMerchantDataFromArray(array $data): array
    {
        return [
            $data[$this->columnsPositions[Report::MERCHANT_ID]],
            $data[$this->columnsPositions[Report::MERCHANT_NAME]],
        ];
    }

    protected function getBatchDataFromArray(array $data, $batchUuid): array
    {
        return [
            $batchUuid,
            $data[$this->columnsPositions[Report::MERCHANT_ID]],
            $data[$this->columnsPositions[Report::BATCH_DATE]],
            $data[$this->columnsPositions[Report::BATCH_REF_NUM]],
        ];
    }

    protected function getTransactionDataFromArray(array $data, $batchUuid): array
    {
        return [
            $batchUuid,
            $data[$this->columnsPositions[Report::TRANSACTION_DATE]],
            $data[$this->columnsPositions[Report::TRANSACTION_TYPE]],
            $data[$this->columnsPositions[Report::TRANSACTION_CARD_TYPE]],
            $data[$this->columnsPositions[Report::TRANSACTION_CARD_NUMBER]],
            $data[$this->columnsPositions[Report::TRANSACTION_AMOUNT]],
        ];
    }

    protected function resolveColumnPositions(array $data, $mapping)
    {
        foreach ($mapping as $key => $value) {
            $this->columnsPositions[$key] = array_search($value, $data);
        }
    }
}
