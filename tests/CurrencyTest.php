<?php
declare(strict_types=1);

namespace B2Binpay\Tests;

use B2Binpay\Currency;
use PHPUnit\Framework\TestCase;

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

    public function setUp()
    {
        $this->currency = new Currency();
        $this->currencyIso = (int)getenv('CURRENCY_ISO');
        $this->currencyAlpha = getenv('CURRENCY_ALPHA');
        $this->currencyName = getenv('CURRENCY_NAME');
        $this->currencyPrecision = (int)getenv('CURRENCY_PRECISION');
    }

    public function tearDown()
    {
        $this->currency = null;
    }

    public function testGetMaxPrecision()
    {
        $this->assertInternalType('int', $this->currency->getMaxPrecision());
    }

    public function testGetAlpha()
    {
        $this->assertSame($this->currencyAlpha, $this->currency->getAlpha($this->currencyIso));
    }

    /**
     * @expectedException \B2Binpay\Exception\UnknownValueException
     */
    public function testGetAlphaException()
    {
        $this->currency->getAlpha(9999);
    }

    public function testGetIso()
    {
        $this->assertSame($this->currencyIso, $this->currency->getIso($this->currencyAlpha));
    }

    /**
     * @expectedException \B2Binpay\Exception\UnknownValueException
     */
    public function testGetIsoException()
    {
        $this->currency->getIso('test');
    }

    public function testGetPrecision()
    {
        $this->assertSame($this->currencyPrecision, $this->currency->getPrecision($this->currencyIso));
    }

    /**
     * @expectedException \B2Binpay\Exception\UnknownValueException
     */
    public function testGetPrecisionException()
    {
        $this->currency->getPrecision(9999);
    }

    public function testGetName()
    {
        $this->assertSame($this->currencyName, $this->currency->getName($this->currencyIso));
    }

    /**
     * @expectedException \B2Binpay\Exception\UnknownValueException
     */
    public function testGetNameException()
    {
        $this->currency->getName(9999);
    }
}
