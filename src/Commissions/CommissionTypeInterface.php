<?php

declare(strict_types=1);

namespace Payme\CommissionFeeCalculator\Commissions;

interface CommissionTypeInterface
{
    public function calculate();
}
