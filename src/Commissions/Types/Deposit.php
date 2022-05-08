<?php

declare(strict_types=1);

namespace Payme\CommissionFeeCalculator\Commissions\Types;

use Payme\CommissionFeeCalculator\Commissions\Commission;
use Payme\CommissionFeeCalculator\Commissions\CommissionTypeInterface;
use Payme\CommissionFeeCalculator\Exceptions\InvalidCurrencyException;
use Payme\CommissionFeeCalculator\Models\Amount;

class Deposit extends Commission implements CommissionTypeInterface
{
    protected const PRIVATE_COMMISSION_PERCENTAGE = 0.03;

    protected const MAX_COMMISSION = [
        'currency' => 'EUR',
        'fee'      => 5,
    ];

    /**
     * @throws InvalidCurrencyException
     */
    public function calculate() : Amount
    {
        $commission    = $this->getFee(self::PRIVATE_COMMISSION_PERCENTAGE);
        $maxCommission = new Amount(self::MAX_COMMISSION['fee'], self::MAX_COMMISSION['currency']);

        if ($this->currencyService->isGreater($commission, $maxCommission)) {
            return $maxCommission;
        }

        return $commission;
    }
}
