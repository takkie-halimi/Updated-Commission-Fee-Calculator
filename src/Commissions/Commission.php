<?php

declare(strict_types=1);

namespace Payme\CommissionFeeCalculator\Commissions;

use Payme\CommissionFeeCalculator\Models\Amount;
use Payme\CommissionFeeCalculator\Models\Operation;
use Payme\CommissionFeeCalculator\Services\CurrencyService;

class Commission
{
    protected Operation $operation;
    protected CurrencyService $currencyService;

    public function __construct(Operation $operation, CurrencyService $currencyService)
    {
        $this->operation       = $operation;
        $this->currencyService = $currencyService;
    }

    protected function getFee(float $rate, ?Amount $feeAbleAmount = null) : Amount
    {
        $amount = $feeAbleAmount ?? $this->operation->getAmount();
        return $this->currencyService->getPercentageOfAmount($amount, $rate);
    }
}
