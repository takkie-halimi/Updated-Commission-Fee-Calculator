<?php

declare(strict_types=1);

namespace Payme\CommissionFeeCalculator\Models;

class Currency
{
    protected string $symbol;
    protected float $rate;
    protected int $precision;

    public function __construct(string $symbol, float $rate)
    {
        $this->symbol = $symbol;
        $this->rate   = $rate;
    }

    public function getRate() : float
    {
        return $this->rate;
    }

    public function getPrecision() : int
    {
        return $this->precision;
    }
}
