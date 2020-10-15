<?php
declare(strict_types=1);

namespace B2Binpay\Tests;

use B2Binpay\Currency;
use PHPUnit\Framework\TestCase;
use B2Binpay\Exception\UnknownValueException;

class CurrencyTest extends TestCase
{
    /**
     * @var Currency
     */
    private $currency;

    private $currencyIso;
    private $currencyAlpha;
    private $currencyName;
    private $currencyPrecision;
    private $unknownCurrencyIso;
    private $unknownCurrencyAlpha;

    public function setUp(): void
    {
        $this->currency = new Currency();
        $this->currencyIso = (int)getenv('CURRENCY_ISO');
        $this->currencyAlpha = getenv('CURRENCY_ALPHA');
        $this->currencyName = getenv('CURRENCY_NAME');
        $this->currencyPrecision = (int)getenv('CURRENCY_PRECISION');
        $this->unknownCurrencyIso = (int)getenv('UNKNOWN_CURRENCY_ISO');
        $this->unknownCurrencyAlpha = getenv('UNKNOWN_CURRENCY_ALPHA');
    }

    public function tearDown(): void
    {
        $this->currency = null;
    }

    public function testGetMaxPrecision()
    {
        $this->assertIsInt($this->currency->getMaxPrecision());
    }

    public function testGetAlpha()
    {
        $this->assertSame($this->currencyAlpha, $this->currency->getAlpha($this->currencyIso));
    }

    public function testGetAlphaException()
    {
        $this->expectException(UnknownValueException::class);
        $this->currency->getAlpha($this->unknownCurrencyIso);
    }

    public function testGetIso()
    {
        $this->assertSame($this->currencyIso, $this->currency->getIso($this->currencyAlpha));
    }

    public function testGetIsoException()
    {
        $this->expectException(UnknownValueException::class);
        $this->currency->getIso($this->unknownCurrencyAlpha);
    }

    public function testGetPrecision()
    {
        $this->assertSame($this->currencyPrecision, $this->currency->getPrecision($this->currencyIso));
    }

    public function testGetPrecisionException()
    {
        $this->expectException(UnknownValueException::class);
        $this->currency->getPrecision(9999);
    }

    public function testGetName()
    {
        $this->assertSame($this->currencyName, $this->currency->getName($this->currencyIso));
    }

    public function testGetNameException()
    {
        $this->expectException(UnknownValueException::class);
        $this->currency->getName($this->unknownCurrencyIso);
    }
}
