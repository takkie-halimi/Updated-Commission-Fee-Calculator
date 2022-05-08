<?php

declare(strict_types=1);

namespace Payme\CommissionFeeCalculator\Services;

use Payme\CommissionFeeCalculator\Exceptions\InvalidCurrencyException;
use Payme\CommissionFeeCalculator\Models\Amount;
use Payme\CommissionFeeCalculator\Models\Currency;

use function array_values;
use function bcadd;
use function bccomp;
use function bcdiv;
use function bcmul;
use function bcpow;
use function bcsub;
use function ceil;
use function number_format;

class CurrencyService
{
    protected const ARITHMETIC_SCALE = 10;
    protected array $currencies      = [];

    public function collectCurrenciesFromArray(array $currencies) : self
    {
        foreach ($currencies as $currency) {
            $this->currencies[$currency['symbol']] = new Currency(...array_values($currency));
        }
        return $this;
    }

    /**
     * @throws InvalidCurrencyException
     */
    public function convert(Amount $amount, string $symbol) : Amount
    {
        $multiplier = bcdiv(
            (string) $this->getCurrencyRateForSymbol($symbol),
            (string) $this->getCurrencyRateForSymbol($amount->getSymbol()),
            self::ARITHMETIC_SCALE
        );

        return new Amount(
            (float) bcmul((string) $amount->getAmount(), $multiplier, self::ARITHMETIC_SCALE),
            $symbol
        );
    }

    public function roundAndFormat(Amount $amount, string $decimalPoint = '.', string $thousandsSeparator = '') : string
    {
        $multiplier = bcpow((string) self::ARITHMETIC_SCALE, '2');
        $newAmount  = bcdiv(
            (string) ceil(
                (float) bcmul(
                    (string) $amount->getAmount(),
                    $multiplier,
                    self::ARITHMETIC_SCALE
                )
            ),
            $multiplier,
            self::ARITHMETIC_SCALE
        );

        return number_format((float) $newAmount, 2, $decimalPoint, $thousandsSeparator);
    }

    public function getPercentageOfAmount(Amount $amount, float $percentage) : Amount
    {
        return new Amount(
            (float) bcmul(
                bcdiv((string) $amount->getAmount(), "100", self::ARITHMETIC_SCALE),
                (string) $percentage,
                self::ARITHMETIC_SCALE
            ),
            $amount->getSymbol()
        );
    }

    /**
     * @throws InvalidCurrencyException
     */
    public function isGreater(Amount $firstAmount, Amount $secondAmount) : bool
    {
        if (
            bccomp(
                (string) $firstAmount->getAmount(),
                (string) $this->convert($secondAmount, $firstAmount->getSymbol())->getAmount(),
                self::ARITHMETIC_SCALE
            ) === 1
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @throws InvalidCurrencyException
     */
    public function sumAmounts(Amount $firstAmount, Amount $secondAmount, string $symbol) : Amount
    {
        return new Amount(
            (float) bcadd(
                (string) $this->convert($firstAmount, $symbol)->getAmount(),
                (string) $this->convert($secondAmount, $symbol)->getAmount(),
                self::ARITHMETIC_SCALE
            ),
            $symbol
        );
    }

    /**
     * @throws InvalidCurrencyException
     */
    public function subAmount(Amount $firstAmount, Amount $secondAmount, string $currencySymbol) : Amount
    {
        return new Amount(
            (float) bcsub(
                (string) $this->convert($firstAmount, $currencySymbol)->getAmount(),
                (string) $this->convert($secondAmount, $currencySymbol)->getAmount(),
                self::ARITHMETIC_SCALE
            ),
            $currencySymbol
        );
    }

    /**
     * @throws InvalidCurrencyException
     */
    private function getCurrencyRateForSymbol(string $symbol) : float
    {
        return $this->getCurrencyOfSymbol($symbol)->getRate();
    }

    /**
     * @throws InvalidCurrencyException
     */
    private function getCurrencyOfSymbol(string $symbol) : object
    {
        if (isset($this->currencies[$symbol])) {
            return $this->currencies[$symbol];
        }
        throw new InvalidCurrencyException();
    }
}
