<?php

namespace Importer;

/**
 * Class Result
 */
class Result
{
    /** @var int Number of imported merchants */
    private int $merchants = 0;

    /** @var int Number of imported batches */
    private int $batches = 0;

    /** @var int Number of imported transactions */
    private int $transactions = 0;

    /**
     * Gets a number of imported merchants
     *
     * @return int Number of imported merchants
     */
    public function getMerchantCount(): int
    {
        return $this->merchants;
    }

    /**
     * @param $value
     */
    public function addMerchantCount($value)
    {
        $this->merchants += $value;
    }

    /**
     * Gets a number of imported batches
     *
     * @return int Number of imported batches
     */
    public function getBatchCount(): int
    {
        return $this->batches;
    }

    /**
     * @param $value
     */
    public function addBatchCount($value)
    {
        $this->batches += $value;
    }

    /**
     * Gets a number of imported transactions
     *
     * @return int Number of imported transactions
     */
    public function getTransactionCount(): int
    {
        return $this->transactions;
    }

    /**
     * @param $value
     */
    public function addTransactionCount($value)
    {
        $this->transactions += $value;
    }
}
