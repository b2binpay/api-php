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
    protected $mock_handler;

    /**
     * @var Client
     */
    protected $client;

    private $currency_iso;
    private $currency_alpha;
    private $currency_precision;

    public function setUp(): void
    {
        $this->mock_handler = new MockHandler();
        $this->client = new Client([
            'handler' => $this->mock_handler,
        ]);

        $this->provider = new Provider(
            getenv('AUTH_KEY'),
            getenv('AUTH_SECRET'),
            true,
            $this->client
        );

        $this->currency_iso = (int)getenv('CURRENCY_ISO');
        $this->currency_alpha = getenv('CURRENCY_ALPHA');
        $this->currency_precision = (int)getenv('CURRENCY_PRECISION');
    }

    public function tearDown(): void
    {
        $this->mock_handler = null;
        $this->client = null;
        $this->provider = null;
    }

    public function testGetAuthorization()
    {
        $this->assertIsString($this->getAuth(), $this->provider->getAuthorization());
    }

    public function testGetAuthToken()
    {
        $response_token = json_encode([
            'token_type' => 'Bearer',
            'access_token' => 'mockToken'
        ]);

        $this->mock_handler->append(
            new Response(200, [], $response_token)
        );

        $this->assertIsString('mockToken', $this->provider->getAuthToken());
    }

    public function testGetRates()
    {
        $response_token = json_encode([
            'token_type' => 'Bearer',
            'access_token' => 'mockToken'
        ]);

        $response_rates = '{ "data": [{ "rate": "deposit" }] }';
        $rates_stub = json_decode($response_rates);

        $this->mock_handler->append(
            new Response(200, [], $response_token),
            new Response(200, [], $response_rates)
        );

        $rates = $this->provider->getRates('USD');
        $this->assertEquals($rates_stub->data, $rates);
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
        list($sum, $currency_from, $currency_to) = $amount;

        $amount = $this->provider->convertCurrency($sum, $currency_from, $currency_to, $this->getRates()->data);
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
        $response_token = json_encode([
            'token_type' => 'Bearer',
            'access_token' => 'mockToken'
        ]);

        $wallet_id = 1;
        $amount = '0.001';
        $currency = $this->currency_alpha;
        $lifetime = 1200;

        $response_bill = '{"data":{"id":13}}';
        $bill_stub = json_decode($response_bill);

        $this->mock_handler->append(
            new Response(200, [], $response_token),
            new Response(200, [], $response_bill)
        );

        $bill = $this->provider->createBill($wallet_id, $amount, $currency, $lifetime);
        $this->assertEquals($bill_stub->data, $bill);
    }

    public function testGetBill()
    {
        $response_token = json_encode([
            'token_type' => 'Bearer',
            'access_token' => 'mockToken'
        ]);

        $bill_id = 1;

        $response_bill = '{ "data": { "id": ' . $bill_id . ' } }';

        $bill_stub = json_decode($response_bill);

        $this->mock_handler->append(
            new Response(200, [], $response_token),
            new Response(200, [], $response_bill)
        );

        $bill = $this->provider->getBill($bill_id);
        $this->assertEquals($bill_stub->data, $bill);
    }

    public function testGetBills()
    {
        $response_token = json_encode([
            'token_type' => 'Bearer',
            'access_token' => 'mockToken'
        ]);

        $response_bills = '{"data":[{"id":1},{"id":13}]}';
        $bills_stub = json_decode($response_bills);

        $this->mock_handler->append(
            new Response(200, [], $response_token),
            new Response(200, [], $response_bills)
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
        $this->assertEquals($bills_stub, $bills);
    }

    public function testCreateWithdrawal()
    {
        $response_token = json_encode([
            'token_type' => 'Bearer',
            'access_token' => 'mockToken'
        ]);

        $virtual_wallet_id = 1;
        $amount = '0.001';
        $currency = $this->currency_alpha;
        $address = 'address';
        $iso = $this->currency_iso;
        $pow = $this->currency_precision;
        $uniqueId = time();

        $tracking_id = null;
        $callback_url = null;
        $message = null;
        $with_fee = true;

        $response_withdrawal = '{
            "data": {
                    "id": 1,
                    "virtual_wallet_id": ' . $virtual_wallet_id . ',
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

        $withdrawal_stub = json_decode($response_withdrawal);

        $this->mock_handler->append(
            new Response(200, [], $response_token),
            new Response(200, [], $response_withdrawal)
        );

        $withdrawal = $this->provider->createWithdrawal($virtual_wallet_id, $amount, $currency, $address, $uniqueId, $tracking_id, $callback_url, $message, $with_fee);
        $this->assertEquals($withdrawal_stub->data, $withdrawal);
    }

    public function testGetWithdrawal()
    {
        $response_token = json_encode([
            'token_type' => 'Bearer',
            'access_token' => 'mockToken'
        ]);

        $withdrawal_id = 1;

        $response_withdrawal = '{"data": { "id": ' . $withdrawal_id . ' } }';
        $withdrawal_stub = json_decode($response_withdrawal);

        $this->mock_handler->append(
            new Response(200, [], $response_token),
            new Response(200, [], $response_withdrawal)
        );

        $withdrawal = $this->provider->getWithdrawal($withdrawal_id);
        $this->assertEquals($withdrawal_stub->data, $withdrawal);
    }

    public function testGetWithdrawals()
    {
        $response_token = json_encode([
            'token_type' => 'Bearer',
            'access_token' => 'mockToken'
        ]);

        $response_withdrawals = '{ "data": [{ "id": 1 }, { "id": 13 }] }';
        $withdrawals_stub = json_decode($response_withdrawals);

        $this->mock_handler->append(
            new Response(200, [], $response_token),
            new Response(200, [], $response_withdrawals)
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
        $this->assertEquals($withdrawals_stub, $withdrawals);
    }

    public function testGetTransfer()
    {
        $response_token = json_encode([
            'token_type' => 'Bearer',
            'access_token' => 'mockToken'
        ]);

        $transfer_id = 1;

        $response_transfer = '{ "data": { "id": ' . $transfer_id . ' } }';
        $transfer_stub = json_decode($response_transfer);

        $this->mock_handler->append(
            new Response(200, [], $response_token),
            new Response(200, [], $response_transfer)
        );

        $transfer = $this->provider->getTransfer($transfer_id);
        $this->assertEquals($transfer_stub->data, $transfer);
    }

    public function testGetTransfers()
    {
        $response_token = json_encode([
            'token_type' => 'Bearer',
            'access_token' => 'mockToken'
        ]);

        $response_transfers = '{ "data": [{ "id": 1 }, { "id": 13 }] }';
        $transfers_stub = json_decode($response_transfers);

        $this->mock_handler->append(
            new Response(200, [], $response_token),
            new Response(200, [], $response_transfers)
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
        $this->assertEquals($transfers_stub, $transfers);
    }

    public function testGetTransaction()
    {
        $response_token = json_encode([
            'token_type' => 'Bearer',
            'access_token' => 'mockToken'
        ]);

        $transaction_id = 1;

        $response_transaction = '{ "data": { "id": ' . $transaction_id . ' } }';
        $transfer_stub = json_decode($response_transaction);

        $this->mock_handler->append(
            new Response(200, [], $response_token),
            new Response(200, [], $response_transaction)
        );

        $transaction = $this->provider->getTransaction($transaction_id);
        $this->assertEquals($transfer_stub->data, $transaction);
    }

    public function testGetTransactions()
    {
        $response_token = json_encode([
            'token_type' => 'Bearer',
            'access_token' => 'mockToken'
        ]);

        $response_transactions = '{ "data": [{ "id": 1 }, { "id": 13 }] }';
        $transactions_stub = json_decode($response_transactions);

        $this->mock_handler->append(
            new Response(200, [], $response_token),
            new Response(200, [], $response_transactions)
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
        $this->assertEquals($transactions_stub, $transactions);
    }

    public function testGetVirtualWallet()
    {
        $response_token = json_encode([
            'token_type' => 'Bearer',
            'access_token' => 'mockToken'
        ]);

        $virtual_wallet_id = 1;

        $response_virtual_wallet = '{ "data": { "id": ' . $virtual_wallet_id . ' } }';
        $virtual_wallet_stub = json_decode($response_virtual_wallet);

        $this->mock_handler->append(
            new Response(200, [], $response_token),
            new Response(200, [], $response_virtual_wallet)
        );

        $virtual_wallet = $this->provider->getVirtualWallet($virtual_wallet_id);
        $this->assertEquals($virtual_wallet_stub->data, $virtual_wallet);
    }

    public function testGetVirtualWallets()
    {
        $response_token = json_encode([
            'token_type' => 'Bearer',
            'access_token' => 'mockToken'
        ]);

        $response_virtual_wallets = '{ "data": [{ "id": 1 }, { "id": 13 }] }';
        $virtual_wallets_stub = json_decode($response_virtual_wallets);

        $this->mock_handler->append(
            new Response(200, [], $response_token),
            new Response(200, [], $response_virtual_wallets)
        );

        $params = [
            'filter' => [
                'created' => date('Y-m-d 00:00:00,Y-m-d 23:59:59')
            ],
            'filter_type' => [
                'created' => 'gt'
            ]
        ];

        $virtual_wallets = $this->provider->getVirtualWallets($params);
        $this->assertEquals($virtual_wallets_stub, $virtual_wallets);
    }

    /**
     * @return string
     */
    private function getAuth()
    {
        return getenv('AUTH');
    }
}
