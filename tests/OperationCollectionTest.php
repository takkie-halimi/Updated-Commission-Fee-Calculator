<?php

declare(strict_types=1);

namespace Payme\CommissionFeeCalculator\Tests;

use Exception;
use Payme\CommissionFeeCalculator\OperationCollection;
use PHPUnit\Framework\TestCase;

final class OperationCollectionTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testCannotParseFromInvalidPath()
    {
        $collection = new OperationCollection();
        $collection->parseFromCSV('invalidPath');
    }

    /**
     * @throws Exception
     */
    public function testCanParseCSVFile()
    {
        $collection = new OperationCollection();
        $collection->parseFromCSV('./input.csv');
        $this->assertIsArray($collection->getOperations());
    }
}
