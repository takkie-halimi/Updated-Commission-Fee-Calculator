<?php

declare(strict_types=1);

namespace Payme\CommissionFeeCalculator\Commissions\Types;

use Payme\CommissionFeeCalculator\Commissions\Commission;
use Payme\CommissionFeeCalculator\Commissions\CommissionTypeInterface;
use Payme\CommissionFeeCalculator\Exceptions\InvalidCurrencyException;
use Payme\CommissionFeeCalculator\Models\Amount;
use Payme\CommissionFeeCalculator\Models\Operation;
use Payme\CommissionFeeCalculator\OperationCollection;
use Payme\CommissionFeeCalculator\Services\CurrencyService;

use function array_filter;

class PrivateWithdraw extends Commission implements CommissionTypeInterface
{
    protected const COMMISSION_PERCENTAGE    = 0.3;
    protected const WEEKLY_FREE_CHARGE_LIMIT = [
        'currency'      => 'EUR',
        'amount'        => 1000,
        'maxOperations' => 3,
    ];

    protected OperationCollection $operationHistory;

    public function __construct(
        Operation $operation,
        CurrencyService $currencyService,
        OperationCollection $operationCollection
    ) {
        $this->operationHistory = $operationCollection;
        parent::__construct($operation, $currencyService);
    }

    /**
     * @throws InvalidCurrencyException
     */
    public function calculate() : Amount
    {
        $summary = $this->getWeeklyWithdrawSummaryOfUser();

        // user has no available free charge limit or allowed for free operation
        if ($summary['availableFreeChargeLimit']->getAmount() <= 0 || $summary['maximumOperationLimitIsReached']) {
            $commission = $this->getFee(self::COMMISSION_PERCENTAGE);
        } else {
            // user has enough limit, free charge
            if ($summary['availableFreeChargeLimit']->getAmount() >= $this->operation->getAmount()->getAmount()) {
                $commission = new Amount(0, $this->operation->getAmountSymbol());
            } else {
                // charge for only exceeded amount
                $exceededAmount = $this->currencyService->subAmount(
                    $this->operation->getAmount(),
                    $summary['availableFreeChargeLimit'],
                    $this->operation->getAmountSymbol()
                );
                $commission     = $this->getFee(self::COMMISSION_PERCENTAGE, $exceededAmount);
            }
        }

        return $commission;
    }

    /**
     * @throws InvalidCurrencyException
     */
    private function getWeeklyWithdrawSummaryOfUser() : array
    {
        // filter the users operations in same week and same type
        $weeklyOperations = $this->getLastWeeksOperations();

        // calculate operation count and total amount of withdraw in same week
        $totalAmount    = new Amount(0, $this->operation->getAmountSymbol());
        $operationCount = 0;
        foreach ($weeklyOperations as $operation) {
            $totalAmount = $this->currencyService->sumAmounts(
                $totalAmount,
                $operation->getAmount(),
                $this->operation->getAmountSymbol()
            );
            $operationCount++;
        }

        // calculate available free charge limit
        $maxLimit                 = new Amount(
            self::WEEKLY_FREE_CHARGE_LIMIT['amount'],
            self::WEEKLY_FREE_CHARGE_LIMIT['currency']
        );
        $availableFreeChargeLimit = $this->currencyService->subAmount(
            $maxLimit,
            $totalAmount,
            $this->operation->getAmountSymbol()
        );

        $maximumOperationLimitIsReached = $operationCount >= self::WEEKLY_FREE_CHARGE_LIMIT['maxOperations'];

        return [
            'maximumOperationLimitIsReached' => $maximumOperationLimitIsReached,
            'availableFreeChargeLimit'       => $availableFreeChargeLimit,
        ];
    }

    private function getLastWeeksOperations() : array
    {
        return array_filter($this->operationHistory->getOperations(), function (Operation $operation) {
            return // must be older than this operation
                $operation->getOperationId() < $this->operation->getOperationId()
                &&
                // processed in same week with this operation
                $operation->getOperationDate()->format('oW') ===
                $this->operation->getOperationDate()->format('oW')
                &&
                // having same operation type
                $operation->getOperationType() === $this->operation->getOperationType()
                &&
                // having the same id with this operation
                $operation->getUserID() === $this->operation->getUserID();
        });
    }
}
