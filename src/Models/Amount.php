<?php

declare(strict_types=1);

namespace Payme\CommissionFeeCalculator\Models;

class Amount
{
    protected float $amount;
    protected string $symbol;

    public function __construct(float $amount, string $symbol)
    {
        $this->amount = $amount;
        $this->symbol = $symbol;
    }

    public function getSymbol() : string
    {
        return $this->symbol;
    }

    public function getAmount() : float
    {
        return $this->amount;
    }
}
