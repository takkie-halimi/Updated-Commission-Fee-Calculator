<?php

require_once __DIR__ . '/vendor/autoload.php';

use Payme\CommissionFeeCalculator\OperationCollection;
use Payme\CommissionFeeCalculator\Services\CurrencyService;
use Payme\CommissionFeeCalculator\Services\CommissionService;
use Payme\CommissionFeeCalculator\Exceptions\InvalidCurrencyException;
use Payme\CommissionFeeCalculator\Exceptions\InvalidOperationTypeException;
use Payme\CommissionFeeCalculator\Exceptions\InvalidUserTypeException;

// Initialize CURL:
$API_URL = curl_init('https://developers.paysera.com/tasks/api/currency-exchange-rates');
curl_setopt($API_URL, CURLOPT_RETURNTRANSFER, true);

// Store the data:
$json = curl_exec($API_URL);
curl_close($API_URL);

// Decode JSON response:
$jsonCurrencies = json_decode($json, true);


$currencies  = [];
foreach ($jsonCurrencies['rates'] as $key => $value) {
    $currencies[] = array('symbol' => $key, 'rate' => $value);
}

    $currencyService = new CurrencyService();
    $currencyService->collectCurrenciesFromArray($currencies);

    $collection = new OperationCollection();
try {
    $collection->parseFromCSV($argv[1]);
} catch (Exception $e) {
}

    $commissionService = new CommissionService($currencyService);

try {
    foreach ($commissionService->calculateFeesFromCollection($collection) as $fee) {
        echo $fee . PHP_EOL;
    }
} catch (InvalidCurrencyException|InvalidOperationTypeException|InvalidUserTypeException $e) {
}
