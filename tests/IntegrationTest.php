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

    private $currencyIso;
    private $currencyAlpha;
    private $currencyPrecision;

    public function setUp(): void
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

        $this->currencyIso = (int)getenv('CURRENCY_ISO');
        $this->currencyAlpha = getenv('CURRENCY_ALPHA');
        $this->currencyPrecision = (int)getenv('CURRENCY_PRECISION');
    }

    public function tearDown(): void
    {
        $this->mockHandler = null;
        $this->client = null;
        $this->provider = null;
    }

    public function testGetAuthorization()
    {
        $this->assertIsString($this->getAuth(), $this->provider->getAuthorization());
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

        $this->assertIsString('mockToken', $this->provider->getAuthToken());
    }

    public function testGetRates()
    {
        $responseToken = json_encode([
            'token_type' => 'Bearer',
            'access_token' => 'mockToken'
        ]);

        $responseRates = '{ "data": [{ "rate": "deposit" }] }';
        $ratesStub = json_decode($responseRates);

        $this->mockHandler->append(
            new Response(200, [], $responseToken),
            new Response(200, [], $responseRates)
        );

        $rates = $this->provider->getRates('USD');
        $this->assertEquals($ratesStub->data, $rates);
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

        $walletId = 1;
        $amount = '0.001';
        $currency = $this->currencyAlpha;
        $lifetime = 1200;

        $responseBill = '{"data":{"id":13}}';
        $billStub = json_decode($responseBill);

        $this->mockHandler->append(
            new Response(200, [], $responseToken),
            new Response(200, [], $responseBill)
        );

        $bill = $this->provider->createBill($walletId, $amount, $currency, $lifetime);
        $this->assertEquals($billStub->data, $bill);
    }

    public function testGetBill()
    {
        $responseToken = json_encode([
            'token_type' => 'Bearer',
            'access_token' => 'mockToken'
        ]);

        $billId = 1;

        $responseBill = '{ "data": { "id": ' . $billId . ' } }';

        $billStub = json_decode($responseBill);

        $this->mockHandler->append(
            new Response(200, [], $responseToken),
            new Response(200, [], $responseBill)
        );

        $bill = $this->provider->getBill($billId);
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

        $params = [
            'filter' => [
                'created' => date('Y-m-d 00:00:00,Y-m-d 23:59:59')
            ],
            'filter_type' => [
                'created' => 'gt'
            ]
        ];

        $bills = $this->provider->getBills($params);
        $this->assertEquals($billsStub, $bills);
    }

    public function testCreateWithdrawal()
    {
        $responseToken = json_encode([
            'token_type' => 'Bearer',
            'access_token' => 'mockToken'
        ]);

        $virtualWalletId = 1;
        $amount = '0.001';
        $currency = $this->currencyAlpha;
        $address = 'address';
        $iso = $this->currencyIso;
        $pow = $this->currencyPrecision;
        $uniqueId = time();

        $trackungId = null;
        $callbackUrl = null;
        $message = null;
        $withFee = true;

        $responseWithdrawal = '{
            "data": {
                    "id": 1,
                    "virtual_wallet_id": ' . $virtualWalletId . ',
                    "with_fee": true,
                    "created": "' . date('Y-m-d H:i:s') . '",
                    "address": "' . $address . '",
                    "message": "' . $message . '",
                    "amount": "' . $amount . '",
                    "fee": "NOT SET",
                    "pow": ' . $pow . ',
                    "status": 0,
                    "transaction": null,
                    "tracking_id": null,
                    "unique_id": ' . $uniqueId . ',
                    "callback_url": null,
                    "currency": {
                        "iso": ' . $iso . ',
                        "alpha": "' . $currency . '"
                    }
                }
            }';

        $withdrawalStub = json_decode($responseWithdrawal);

        $this->mockHandler->append(
            new Response(200, [], $responseToken),
            new Response(200, [], $responseWithdrawal)
        );

        $withdrawal = $this->provider->createWithdrawal($virtualWalletId, $amount, $currency, $address, $uniqueId, $trackungId, $callbackUrl, $message, $withFee);
        $this->assertEquals($withdrawalStub->data, $withdrawal);
    }

    public function testGetWithdrawal()
    {
        $responseToken = json_encode([
            'token_type' => 'Bearer',
            'access_token' => 'mockToken'
        ]);

        $withdrawalId = 1;

        $responseWithdrawal = '{"data": { "id": ' . $withdrawalId . ' } }';
        $withdrawalStub = json_decode($responseWithdrawal);

        $this->mockHandler->append(
            new Response(200, [], $responseToken),
            new Response(200, [], $responseWithdrawal)
        );

        $withdrawal = $this->provider->getWithdrawal($withdrawalId);
        $this->assertEquals($withdrawalStub->data, $withdrawal);
    }

    public function testGetWithdrawals()
    {
        $responseToken = json_encode([
            'token_type' => 'Bearer',
            'access_token' => 'mockToken'
        ]);

        $responseWithdrawals = '{ "data": [{ "id": 1 }, { "id": 13 }] }';
        $withdrawalsStub = json_decode($responseWithdrawals);

        $this->mockHandler->append(
            new Response(200, [], $responseToken),
            new Response(200, [], $responseWithdrawals)
        );

        $params = [
            'filter' => [
                'created' => date('Y-m-d 00:00:00,Y-m-d 23:59:59')
            ],
            'filter_type' => [
                'created' => 'gt'
            ]
        ];

        $withdrawals = $this->provider->getWithdrawals($params);
        $this->assertEquals($withdrawalsStub, $withdrawals);
    }

    public function testGetTransfer()
    {
        $responseToken = json_encode([
            'token_type' => 'Bearer',
            'access_token' => 'mockToken'
        ]);

        $transferId = 1;

        $responseTransfer = '{ "data": { "id": ' . $transferId . ' } }';
        $transferStub = json_decode($responseTransfer);

        $this->mockHandler->append(
            new Response(200, [], $responseToken),
            new Response(200, [], $responseTransfer)
        );

        $transfer = $this->provider->getTransfer($transferId);
        $this->assertEquals($transferStub->data, $transfer);
    }

    public function testGetTransfers()
    {
        $responseToken = json_encode([
            'token_type' => 'Bearer',
            'access_token' => 'mockToken'
        ]);

        $responseTransfers = '{ "data": [{ "id": 1 }, { "id": 13 }] }';
        $transfersStub = json_decode($responseTransfers);

        $this->mockHandler->append(
            new Response(200, [], $responseToken),
            new Response(200, [], $responseTransfers)
        );

        $params = [
            'filter' => [
                'created' => date('Y-m-d 00:00:00,Y-m-d 23:59:59')
            ],
            'filter_type' => [
                'created' => 'gt'
            ]
        ];

        $transfers = $this->provider->getTransfers($params);
        $this->assertEquals($transfersStub, $transfers);
    }

    public function testGetTransaction()
    {
        $responseToken = json_encode([
            'token_type' => 'Bearer',
            'access_token' => 'mockToken'
        ]);

        $transactionId = 1;

        $responseTransaction = '{ "data": { "id": ' . $transactionId . ' } }';
        $transferStub = json_decode($responseTransaction);

        $this->mockHandler->append(
            new Response(200, [], $responseToken),
            new Response(200, [], $responseTransaction)
        );

        $transaction = $this->provider->getTransaction($transactionId);
        $this->assertEquals($transferStub->data, $transaction);
    }

    public function testGetTransactions()
    {
        $responseToken = json_encode([
            'token_type' => 'Bearer',
            'access_token' => 'mockToken'
        ]);

        $responseTransactions = '{ "data": [{ "id": 1 }, { "id": 13 }] }';
        $transactionsStub = json_decode($responseTransactions);

        $this->mockHandler->append(
            new Response(200, [], $responseToken),
            new Response(200, [], $responseTransactions)
        );

        $params = [
            'filter' => [
                'created' => date('Y-m-d 00:00:00,Y-m-d 23:59:59')
            ],
            'filter_type' => [
                'created' => 'gt'
            ]
        ];

        $transactions = $this->provider->getTransactions($params);
        $this->assertEquals($transactionsStub, $transactions);
    }

    /**
     * @return string
     */
    private function getAuth()
    {
        return getenv('AUTH');
    }
}
