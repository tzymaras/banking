<?php
namespace App\Services;

use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money;

class CurrencyService
{

    /**
     * General purpose function to format an amount of money according to the given locale and currency
     *
     * @param int    $amount
     * @param string $currency
     * @param string $locale
     * @return string
     */
    public static function formatMoney(int $amount, string $currency = 'EUR', string $locale = 'de_DE'): string
    {
        $money = new Money($amount, new Currency($currency));

        $moneyFormatter = new IntlMoneyFormatter(
            new \NumberFormatter($locale, \NumberFormatter::CURRENCY),
            new ISOCurrencies()
        );

        return $moneyFormatter->format($money);
    }
}