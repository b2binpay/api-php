<?php

namespace B2Binpay\Tests;

use B2Binpay\Provider;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;

class IntegrationTest extends TestCase
{
    /**
     * @var Provider
     */
    protected $provider;

    /**
     * @var MockHandler
     */
    protected $mockHandler;

    /**
     * @var Client
     */
    protected $client;

    public function setUp()
    {
        $this->mockHandler = new MockHandler();
        $this->client = new Client([
            'handler' => $this->mockHandler,
        ]);

        $this->provider = new Provider(
            getenv('AUTH_KEY'),
            getenv('AUTH_SECRET'),
            true,
            $this->client
        );
    }

    public function tearDown()
    {
        $this->mockHandler = null;
        $this->client = null;
        $this->provider = null;
    }

    public function testGetAuthorization()
    {
        $this->assertSame($this->getAuth(), $this->provider->getAuthorization());
    }

    public function testGetAuthToken()
    {
        $responseToken = json_encode([
            'token_type' => 'Bearer',
            'access_token' => 'mockToken'
        ]);

        $this->mockHandler->append(
            new Response(200, [], $responseToken)
        );

        $this->assertSame('mockToken', $this->provider->getAuthToken());
    }

    public function testGetRates()
    {
        $responseToken = json_encode([
            'token_type' => 'Bearer',
            'access_token' => 'mockToken'
        ]);

        $ratesStub = ['rate' => 'deposit'];
        $responseRates = json_encode(['data' => $ratesStub]);

        $this->mockHandler->append(
            new Response(200, [], $responseToken),
            new Response(200, [], $responseRates)
        );

        $rates = $this->provider->getRates('USD');
        $this->assertEquals($ratesStub, get_object_vars($rates));
    }

    public function testGetWallets()
    {
        $responseToken = json_encode([
            'token_type' => 'Bearer',
            'access_token' => 'mockToken'
        ]);

        $responseWallets = '{"data":[{"id":1},{"id":2}]}';
        $walletsStub = json_decode($responseWallets);

        $this->mockHandler->append(
            new Response(200, [], $responseToken),
            new Response(200, [], $responseWallets)
        );

        $wallets = $this->provider->getWallets();
        $this->assertEquals($walletsStub->data, $wallets);
    }

    public function testGetWallet()
    {
        $responseToken = json_encode([
            'token_type' => 'Bearer',
            'access_token' => 'mockToken'
        ]);

        $responseWallet = '{"data":{"id":1}}';
        $walletStub = json_decode($responseWallet);

        $this->mockHandler->append(
            new Response(200, [], $responseToken),
            new Response(200, [], $responseWallet)
        );

        $wallet = $this->provider->getWallet(1);
        $this->assertEquals($walletStub->data, $wallet);
    }

    private function getRates()
    {
        return json_decode(
            '{"data":[{
                "from":{"alpha":"USD","iso":840},
                "to":{"alpha":"XRP","iso":1010},
                "rate":"264866406",
                "pow":8
            },{
                "from":{"alpha":"USD","iso":840},
                "to":{"alpha":"BTC","iso":1000},
                "rate":"15242",
                "pow":8
            },{
                "from":{"alpha":"USD","iso":840},
                "to":{"alpha":"NEO","iso":2014},
                "rate":"5100570",
                "pow":8
            },{
                "from":{"alpha":"USD","iso":840},
                "to":{"alpha":"B2BX","iso":2000},
                "rate":"123456789012345678",
                "pow":18
            },{
                "from":{"alpha":"USD","iso":840},
                "to":{"alpha":"LTC","iso":1003},
                "rate":"1807564",
                "pow":8
            }]}'
        );
    }

    public function convertCurrencyDataProvider()
    {
        return [
            [
                'amount' => ['1', 'USD', 'USD'],
                'expect' => '1.00'
            ], [
                'amount' => ['2', 'USD', 'XRP'],
                'expect' => '5.297329'
            ], [
                'amount' => ['3', 'USD', 'BTC'],
                'expect' => '0.00045726'
            ], [
                'amount' => ['4', 'USD', 'LTC'],
                'expect' => '0.07230256'
            ], [
                'amount' => ['1', 'USD', 'B2BX'],
                'expect' => '0.123456789012345678'
            ], [
                'amount' => ['0.01', 'USD', 'NEO'],
                'expect' => '0.001'
            ]
        ];
    }

    /**
     * @dataProvider convertCurrencyDataProvider
     * @param array $amount
     * @param string $expect
     */
    public function testConvertCurrency(array $amount, string $expect)
    {
        list($sum, $currencyFrom, $currencyTo) = $amount;

        $amount = $this->provider->convertCurrency($sum, $currencyFrom, $currencyTo, $this->getRates()->data);
        $this->assertSame($expect, $amount);
    }

    public function addMarkupDataProvider()
    {
        return [
            [
                'amount' => ['1.00', 'USD', 10],
                'expect' => '1.10'
            ], [
                'amount' => ['2.10', 'EUR', 20],
                'expect' => '2.52'
            ], [
                'amount' => ['0.0000000000000001', 'B2BX', 35],
                'expect' => '0.000000000000000135'
            ], [
                'amount' => ['0.1', 'NEO', 95],
                'expect' => '0.195'
            ], [
                'amount' => ['0.01', 'NEO', 98],
                'expect' => '0.020'
            ]
        ];
    }

    /**
     * @dataProvider addMarkupDataProvider
     * @param $amount
     * @param $expect
     */
    public function testAddMarkup($amount, $expect)
    {
        list($sum, $currency, $percent) = $amount;

        $amount = $this->provider->addMarkup($sum, $currency, $percent);
        $this->assertSame($expect, $amount);
    }

    public function testCreateBill()
    {
        $responseToken = json_encode([
            'token_type' => 'Bearer',
            'access_token' => 'mockToken'
        ]);

        $responseBill = '{"data":{"id":13}}';
        $billStub = json_decode($responseBill);

        $this->mockHandler->append(
            new Response(200, [], $responseToken),
            new Response(200, [], $responseBill)
        );

        $bill = $this->provider->createBill(1, '0.00000001', 'BTC', 1200);
        $this->assertEquals($billStub->data, $bill);
    }

    public function testGetBill()
    {
        $responseToken = json_encode([
            'token_type' => 'Bearer',
            'access_token' => 'mockToken'
        ]);

        $responseBill = '{"data":{"id":13}}';
        $billStub = json_decode($responseBill);

        $this->mockHandler->append(
            new Response(200, [], $responseToken),
            new Response(200, [], $responseBill)
        );

        $bill = $this->provider->getBill(1);
        $this->assertEquals($billStub->data, $bill);
    }

    public function testGetBills()
    {
        $responseToken = json_encode([
            'token_type' => 'Bearer',
            'access_token' => 'mockToken'
        ]);

        $responseBills = '{"data":[{"id":1},{"id":13}]}';
        $billsStub = json_decode($responseBills);

        $this->mockHandler->append(
            new Response(200, [], $responseToken),
            new Response(200, [], $responseBills)
        );

        $bills = $this->provider->getBills(1);
        $this->assertEquals($billsStub->data, $bills);
    }

    /**
     * @return string
     */
    private function getAuth()
    {
        return getenv('AUTH');
    }
}
