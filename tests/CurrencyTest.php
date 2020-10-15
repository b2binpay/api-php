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

    private $currency_iso;
    private $currency_alpha;
    private $currency_name;
    private $currency_precision;
    private $unknown_currency_iso;
    private $unknown_currency_alpha;

    public function setUp(): void
    {
        $this->currency = new Currency();
        $this->currency_iso = (int)getenv('CURRENCY_ISO');
        $this->currency_alpha = getenv('CURRENCY_ALPHA');
        $this->currency_name = getenv('CURRENCY_NAME');
        $this->currency_precision = (int)getenv('CURRENCY_PRECISION');
        $this->unknown_currency_iso = (int)getenv('UNKNOWN_CURRENCY_ISO');
        $this->unknown_currency_alpha = getenv('UNKNOWN_CURRENCY_ALPHA');
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
        $this->assertSame($this->currency_alpha, $this->currency->getAlpha($this->currency_iso));
    }

    public function testGetAlphaException()
    {
        $this->expectException(UnknownValueException::class);
        $this->currency->getAlpha($this->unknown_currency_iso);
    }

    public function testGetIso()
    {
        $this->assertSame($this->currency_iso, $this->currency->getIso($this->currency_alpha));
    }

    public function testGetIsoException()
    {
        $this->expectException(UnknownValueException::class);
        $this->currency->getIso($this->unknown_currency_alpha);
    }

    public function testGetPrecision()
    {
        $this->assertSame($this->currency_precision, $this->currency->getPrecision($this->currency_iso));
    }

    public function testGetPrecisionException()
    {
        $this->expectException(UnknownValueException::class);
        $this->currency->getPrecision(9999);
    }

    public function testGetName()
    {
        $this->assertSame($this->currency_name, $this->currency->getName($this->currency_iso));
    }

    public function testGetNameException()
    {
        $this->expectException(UnknownValueException::class);
        $this->currency->getName($this->unknown_currency_iso);
    }
}
