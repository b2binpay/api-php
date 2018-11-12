<?php
declare(strict_types=1);

namespace B2Binpay\Tests;

use B2Binpay\Amount;
use B2Binpay\AmountFactory;
use B2Binpay\Currency;
use B2Binpay\Provider;
use B2Binpay\ApiInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class ProviderTest extends TestCase
{
    /**
     * @var Provider
     */
    protected $provider;

    /**
     * @var Currency | MockObject
     */
    protected $currency;

    /**
     * @var AmountFactory | MockObject
     */
    protected $amountFactory;

    /**
     * @var ApiInterface | MockObject
     */
    protected $api;

    public function setUp()
    {
        $this->currency = $this->createMock(Currency::class);
        $this->amountFactory = $this->createMock(AmountFactory::class);
        $this->api = $this->createMock(ApiInterface::class);

        $this->provider = new Provider(
            getenv('AUTH_KEY'),
            getenv('AUTH_SECRET'),
            true,
            null,
            $this->currency,
            $this->amountFactory,
            $this->api
        );
    }

    public function tearDown()
    {
        $this->provider = null;
        $this->currency = null;
        $this->amountFactory = null;
        $this->api = null;
    }

    public function testGetAuthorization()
    {
        $this->api->method('genAuthBasic')
            ->willReturn($this->getAuthBasic());

        $this->assertSame($this->getAuth(), $this->provider->getAuthorization());
    }

    public function testGetAuthToken()
    {
        $this->api->method('getAccessToken')
            ->willReturn($this->getAuthBasic());

        $this->assertSame($this->getAuthBasic(), $this->provider->getAuthToken());
    }

    public function testGetRates()
    {
        $url = 'url';
        $alpha = 'btc';
        $response = [1, 2];

        $this->currency->method('getIso')
            ->willReturn($this->getCurrencyIso());

        $this->currency->method('getAlpha')
            ->willReturn($alpha);

        $this->api->method('getRatesUrl')
            ->willReturn($url);

        $this->api->expects($this->once())
            ->method('sendRequest')
            ->with(
                $this->equalTo('get'),
                $this->equalTo($url . $alpha)
            )
            ->willReturn($response);
        
        $rates = $this->provider->getRates('currency');
        $this->assertEquals($response, $rates);
    }

    public function testGetWallets()
    {
        $url = 'url';
        $response = [1, 2];

        $this->api->method('getWalletsUrl')
            ->willReturn($url);

        $this->api->expects($this->once())
            ->method('sendRequest')
            ->with(
                $this->equalTo('get'),
                $this->equalTo($url)
            )
            ->willReturn($response);

        $wallets = $this->provider->getWallets();
        $this->assertEquals($response, $wallets);
    }

    public function testGetWallet()
    {
        $url = 'url';
        $wallet = 1;
        $response = [1, 2];

        $this->api->method('getWalletsUrl')
            ->willReturn($url);

        $this->api->expects($this->once())
            ->method('sendRequest')
            ->with(
                $this->equalTo('get'),
                $this->equalTo($url)
            )
            ->willReturn($response);

        $wallets = $this->provider->getWallet($wallet);
        $this->assertEquals($response, $wallets);
    }

    public function testConvertCurrencySame()
    {
        $this->currency->method('getIso')
            ->willReturn(1);

        $amountObj = $this->createMock(Amount::class);

        $this->amountFactory->method('create')
            ->willReturn($amountObj);

        $amountObj->method('getValue')
            ->willReturn('value');

        $amount = $this->provider->convertCurrency('1', 'USD', 'USD', []);
        $this->assertSame('value', $amount);
    }

    public function testConvertCurrency()
    {
        $sum = '0.001';
        $currencyFrom = 'USD';
        $currencyTo = 'XRP';
        $isoFrom = 840;
        $isoTo = 1010;
        $rate = '264866406';
        $precision = 8;
        $result = '1234';

        $ratesStub = json_decode(
            '{"data":[{
                "from":{"alpha":"USD","iso":840},
                "to":{"alpha":"XRP","iso":1010},
                "rate":"264866406",
                "pow":8
            }]}'
        );

        $this->currency->method('getIso')
            ->will($this->onConsecutiveCalls($isoFrom, $isoTo));

        $inputAmount = $this->createMock(Amount::class);
        $rateAmount = $this->createMock(Amount::class);
        $resultAmount = $this->createMock(Amount::class);

        $this->amountFactory->expects($this->exactly(2))
            ->method('create')
            ->withConsecutive(
                [$this->equalTo($sum), $this->equalTo($isoFrom)],
                [$this->equalTo($rate), $this->isNull(), $this->equalTo($precision)]
            )
            ->will($this->onConsecutiveCalls($inputAmount, $rateAmount));

        $this->currency->method('getPrecision')
            ->willReturn($precision);

        $inputAmount->expects($this->once())
            ->method('convert')
            ->with(
                $this->equalTo($rateAmount),
                $this->equalTo($precision)
            )
            ->willReturn($resultAmount);

        $resultAmount->method('getValue')
            ->willReturn($result);

        $amount = $this->provider->convertCurrency($sum, $currencyFrom, $currencyTo, $ratesStub->data);
        $this->assertSame($result, $amount);
    }

    /**
     * @expectedException \B2Binpay\Exception\IncorrectRatesException
     */
    public function testIncorrectRatesException()
    {
        $this->currency->method('getIso')
            ->will($this->onConsecutiveCalls(1, 2));

        $amountObj = $this->createMock(Amount::class);

        $this->amountFactory->method('create')
            ->willReturn($amountObj);

        $this->provider->convertCurrency('1', 'USD', 'BTC', []);
    }

    public function testAddMarkup()
    {
        $sum = '0.001';
        $iso = 840;
        $percent = 10;
        $result = '9999';

        $this->currency->method('getIso')
            ->willReturn($iso);

        $amountObj = $this->createMock(Amount::class);
        $resultAmount = $this->createMock(Amount::class);

        $this->amountFactory->expects($this->once())
            ->method('create')
            ->with(
                $this->equalTo($sum),
                $this->equalTo($iso)
            )
            ->willReturn($amountObj);

        $amountObj->expects($this->once())
            ->method('percentage')
            ->with(
                $this->equalTo($percent)
            )
            ->willReturn($resultAmount);

        $resultAmount->method('getValue')
            ->willReturn($result);

        $amount = $this->provider->addMarkup($sum, 'USD', $percent);
        $this->assertSame($result, $amount);
    }

    public function testCreateBill()
    {
        $url = 'url';
        $response = [1, 2];
        $amount = '123';
        $precision = 8;
        $wallet = 1;
        $lifetime = 1200;
        $trackingId = 'trackingId';
        $callbackUrl = 'callbackUrl';

        $params = [
            'amount' => $amount,
            'wallet' => $wallet,
            'pow' => $precision,
            'lifetime' => $lifetime,
            'tracking_id' => $trackingId,
            'callback_url' => $callbackUrl
        ];

        $this->currency->method('getIso')
            ->willReturn($this->getCurrencyIso());

        $this->api->method('getNewBillUrl')
            ->willReturn($url);

        $amountObj = $this->createMock(Amount::class);

        $this->amountFactory->method('create')
            ->willReturn($amountObj);

        $amountObj->method('getPowed')
            ->willReturn($amount);

        $amountObj->method('getPrecision')
            ->willReturn($precision);

        $this->api->expects($this->once())
            ->method('sendRequest')
            ->with(
                $this->equalTo('post'),
                $this->equalTo($url),
                $this->equalTo($params)
            )
            ->willReturn($response);

        $bill = $this->provider->createBill($wallet, $amount, 'BTC', $lifetime, $trackingId, $callbackUrl);
        $this->assertEquals($response, $bill);
    }

    public function testGetBills()
    {
        $url = 'url';
        $wallet = 1;
        $response = [1, 2];

        $this->api->method('getBillsUrl')
            ->willReturn($url);

        $this->api->expects($this->once())
            ->method('sendRequest')
            ->with(
                $this->equalTo('get'),
                $this->equalTo($url)
            )
            ->willReturn($response);

        $bills = $this->provider->getBills($wallet);
        $this->assertEquals($response, $bills);
    }

    public function testGetBill()
    {
        $url = 'url';
        $billId = 1;
        $response = [1, 2];

        $this->api->method('getBillsUrl')
            ->willReturn($url);

        $this->api->expects($this->once())
            ->method('sendRequest')
            ->with(
                $this->equalTo('get'),
                $this->equalTo($url)
            )
            ->willReturn($response);

        $bill = $this->provider->getBill($billId);
        $this->assertEquals($response, $bill);
    }

    /**
     * @return string
     */
    private function getAuth()
    {
        return getenv('AUTH');
    }

    /**
     * @return string
     */
    private function getAuthBasic()
    {
        return getenv('AUTH_BASIC');
    }

    /**
     * @return string
     */
    private function getCurrencyIso()
    {
        return getenv('CURRENCY_ISO');
    }
}
