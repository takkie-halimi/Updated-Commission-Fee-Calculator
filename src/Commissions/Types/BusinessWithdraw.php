<?php

declare(strict_types=1);

namespace Payme\CommissionFeeCalculator\Commissions\Types;

use Payme\CommissionFeeCalculator\Commissions\Commission;
use Payme\CommissionFeeCalculator\Commissions\CommissionTypeInterface;
use Payme\CommissionFeeCalculator\Exceptions\InvalidCurrencyException;
use Payme\CommissionFeeCalculator\Models\Amount;

class BusinessWithdraw extends Commission implements CommissionTypeInterface
{
    protected const COMMISSION_PERCENTAGE = 0.5;
    protected const MIN_COMMISSION        = [
        'currency' => 'EUR',
        'fee'      => 0.5,
    ];

    /**
     * @throws InvalidCurrencyException
     */
    public function calculate() : Amount
    {
        $commission    = $this->getFee(self::COMMISSION_PERCENTAGE);
        $minCommission = new Amount(self::MIN_COMMISSION['fee'], self::MIN_COMMISSION['currency']);

        if ($this->currencyService->isGreater($minCommission, $commission)) {
            return $minCommission;
        }

        return $commission;
    }
}
