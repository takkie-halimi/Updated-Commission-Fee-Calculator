<?php

declare(strict_types=1);

namespace Payme\CommissionFeeCalculator\Tests\Commissions\Types;

use Payme\CommissionFeeCalculator\Commissions\Types\BusinessWithdraw;
use Payme\CommissionFeeCalculator\Exceptions\InvalidCurrencyException;
use Payme\CommissionFeeCalculator\Models\Amount;
use Payme\CommissionFeeCalculator\Models\Operation;
use Payme\CommissionFeeCalculator\Services\CurrencyService;
use PHPUnit\Framework\TestCase;

final class BusinessWithdrawTest extends TestCase
{
    protected CurrencyService $currencyService;
    protected Operation $operation;
    protected Amount $amount;

    public function setUp()
    {
        $this->currencyService = $this->createMock(CurrencyService::class);
        $this->operation       = $this->createMock(Operation::class);
        $this->amount          = $this->createMock(Amount::class);
    }

    /**
     * @throws InvalidCurrencyException
     */
    public function testWillReturnAmount()
    {
        $this->operation
            ->expects($this->atLeastOnce())
            ->method('getAmount')
            ->willReturn($this->amount);

        $this->currencyService
            ->expects($this->atLeastOnce())
            ->method('isGreater')
            ->will($this->returnValue(true));

        $this->currencyService
            ->expects($this->atLeastOnce())
            ->method('getPercentageOfAmount')
            ->willReturn($this->amount);

        $commission = new BusinessWithdraw($this->operation, $this->currencyService);
        $commission->calculate();

        $this->assertInstanceOf(
            Amount::class,
            $commission->calculate()
        );
    }
}
