<?php

declare(strict_types=1);

namespace Payme\CommissionFeeCalculator;

use DateTime;
use Exception;
use League\Csv\Reader;
use Payme\CommissionFeeCalculator\Exceptions\FileNotFoundException;
use Payme\CommissionFeeCalculator\Models\Amount;
use Payme\CommissionFeeCalculator\Models\Operation;

use function end;
use function file_exists;

class OperationCollection
{
    protected array $operations = [];

    /**
     * @throws Exception
     */
    public function parseFromCSV(string $path, bool $append = false)
    {
        if (! file_exists($path)) {
            throw new FileNotFoundException();
        }
        $this->operations = $append ? $this->operations : [];
        foreach (Reader::createFromPath($path) as $line) {
            $this->add(new Operation(
                $this->generateTransactionID(),
                new DateTime($line[0]),
                (int) $line[1],
                $line[2],
                $line[3],
                new Amount((float) $line[4], $line[5])
            ));
        }
    }

    public function add(Operation $operation) : self
    {
        $this->operations[] = $operation;
        return $this;
    }

    public function getOperations() : array
    {
        return $this->operations;
    }

    private function generateTransactionID() : int
    {
        $operations = $this->getOperations();
        return $this->isEmpty() ? 1 : end($operations)->getOperationId() + 1;
    }

    private function isEmpty() : bool
    {
        return empty($this->getOperations());
    }
}
