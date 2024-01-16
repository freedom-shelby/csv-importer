<?php

use Dotenv\Dotenv;
use Importer\DatabaseConnectionFactory;
use Importer\Importer;
use Importer\Report;

include __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$filename = __DIR__ . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'report.csv';
$mapping  = [
    Report::TRANSACTION_DATE        => 'Transaction Date',
    Report::TRANSACTION_TYPE        => 'Transaction Type',
    Report::TRANSACTION_CARD_TYPE   => 'Transaction Card Type',
    Report::TRANSACTION_CARD_NUMBER => 'Transaction Card Number',
    Report::TRANSACTION_AMOUNT      => 'Transaction Amount',
    Report::BATCH_DATE              => 'Batch Date',
    Report::BATCH_REF_NUM           => 'Batch Reference Number',
    Report::MERCHANT_ID             => 'Merchant ID',
    Report::MERCHANT_NAME           => 'Merchant Name',
];

// database credentials are located in docker-compose.yml and loaded by phpdotenv package
$dbConnection = (new DatabaseConnectionFactory())->makeConnection(
    getenv('DB_HOST'),
    getenv('DB_NAME'),
    getenv('DB_USER'),
    getenv('DB_PASS')
);

$importer = new Importer($dbConnection);
$result   = $importer->process($filename, $mapping);

echo sprintf(
    'Imported %d merchants, %d batches, and %d transactions' . PHP_EOL,
    $result->getMerchantCount(),
    $result->getBatchCount(),
    $result->getTransactionCount()
);
