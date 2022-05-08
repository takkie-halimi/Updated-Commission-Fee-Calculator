<?php

declare(strict_types=1);

namespace Payme\CommissionFeeCalculator\Tests\Commissions\Types;

use Payme\CommissionFeeCalculator\Commissions\Types\PrivateWithdraw;
use Payme\CommissionFeeCalculator\Exceptions\InvalidCurrencyException;
use Payme\CommissionFeeCalculator\Models\Amount;
use Payme\CommissionFeeCalculator\Models\Operation;
use Payme\CommissionFeeCalculator\OperationCollection;
use Payme\CommissionFeeCalculator\Services\CurrencyService;
use PHPUnit\Framework\TestCase;

final class PrivateWithdrawTest extends TestCase
{
    protected CurrencyService $currencyService;
    protected Operation $operation;
    protected OperationCollection $operationCollection;
    protected Amount $amount;

    public function setUp()
    {
        $this->currencyService     = $this->createMock(CurrencyService::class);
        $this->operation           = $this->createMock(Operation::class);
        $this->operationCollection = $this->createMock(OperationCollection::class);
        $this->amount              = $this->createMock(Amount::class);
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

        $this->operationCollection
            ->expects($this->atLeastOnce())
            ->method('getOperations')
            ->willReturn([$this->operation]);

        $this->currencyService
            ->expects($this->atLeastOnce())
            ->method('subAmount')
            ->willReturn($this->amount);

        $this->currencyService
            ->expects($this->atLeastOnce())
            ->method('getPercentageOfAmount')
            ->willReturn($this->amount);

        $commission = new PrivateWithdraw(
            $this->operation,
            $this->currencyService,
            $this->operationCollection
        );

        $commission->calculate();

        $this->assertInstanceOf(
            Amount::class,
            $commission->calculate()
        );
    }
}
