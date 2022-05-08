<?php

declare(strict_types=1);

namespace Payme\CommissionFeeCalculator\Models;

use DateTime;

class Operation
{
    protected int $operationId;
    protected DateTime $operationDate;
    protected int $userId;
    protected string $userType;
    protected string $operationType;
    protected Amount $amount;

    public function __construct(
        int $operationId,
        DateTime $operationDate,
        int $userId,
        string $userType,
        string $operationType,
        Amount $amount
    ) {
        $this->operationId   = $operationId;
        $this->operationDate = $operationDate;
        $this->userId        = $userId;
        $this->userType      = $userType;
        $this->operationType = $operationType;
        $this->amount        = $amount;
    }

    public function getOperationId() : int
    {
        return $this->operationId;
    }

    public function getUserType() : string
    {
        return $this->userType;
    }

    public function getOperationType() : string
    {
        return $this->operationType;
    }

    public function getAmount() : Amount
    {
        return $this->amount;
    }

    public function getAmountSymbol() : string
    {
        return $this->amount->getSymbol();
    }

    public function getUserID() : int
    {
        return $this->userId;
    }

    public function getOperationDate() : DateTime
    {
        return $this->operationDate;
    }
}
