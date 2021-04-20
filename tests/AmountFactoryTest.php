<?php
declare(strict_types=1);

namespace B2Binpay\Tests;

use B2Binpay\Amount;
use B2Binpay\AmountFactory;
use B2Binpay\Currency;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class AmountFactoryTest extends TestCase
{
    /**
     * @var Currency | MockObject
     */
    private $currency;

    public function setUp(): void
    {
        $this->currency = $this->createMock(Currency::class);
    }

    public function tearDown(): void
    {
        $this->currency = null;
    }

    public function testCreate()
    {
        $amount = (new AmountFactory($this->currency))->create('1');
        $this->assertInstanceOf(Amount::class, $amount);
    }
}
