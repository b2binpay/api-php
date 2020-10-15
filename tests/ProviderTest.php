<?php
declare(strict_types=1);

namespace B2Binpay\Tests;

use B2Binpay\Amount;
use B2Binpay\AmountFactory;
use B2Binpay\Currency;
use B2Binpay\Exception\IncorrectRatesException;
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
    protected $amount_factory;

    /**
     * @var Amount | MockObject
     */
    protected $amount;

    /**
     * @var ApiInterface | MockObject
     */
    protected $api;

    private $currency_iso;
    private $currency_alpha;
    private $currency_precision;

    private $sign_time;
    private $sign_hash;

    public function setUp(): void
    {
        $this->currency = $this->createMock(Currency::class);
        $this->amount_factory = $this->createMock(AmountFactory::class);
        $this->amount = $this->createMock(Amount::class);
        $this->api = $this->createMock(ApiInterface::class);

        $this->provider = new Provider(
            getenv('AUTH_KEY'),
            getenv('AUTH_SECRET'),
            true,
            null,
            $this->currency,
            $this->amount_factory,
            $this->api
        );

        $this->currency_iso = (int)getenv('CURRENCY_ISO');
        $this->currency_alpha = getenv('CURRENCY_ALPHA');
        $this->currency_precision = (int)getenv('CURRENCY_PRECISION');

        $this->sign_time = getenv('SIGN_TIME');
        $this->sign_hash = getenv('SIGN_HASH');
    }

    public function tearDown(): void
    {
        $this->provider = null;
        $this->currency = null;
        $this->amount_factory = null;
        $this->amount = null;
        $this->api = null;
    }

    public function testGetAuthorization()
    {
        $this->api->method('genAuthBasic')
            ->willReturn($this->getAuthBasic());

        $this->assertIsString($this->getAuth(), $this->provider->getAuthorization());
    }

    public function testGetAuthToken()
    {
        $this->api->method('getAccessToken')
            ->willReturn($this->getAuthBasic());

        $this->assertIsString($this->getAuthBasic(), $this->provider->getAuthToken());
    }

    public function testGetRates()
    {
        $url = 'url';
        $currency = strtolower($this->currency_alpha);

        $response_rates = '{ "data": [{ "rate": "deposit" }] }';
        $rates_stub = json_decode($response_rates);

        $this->api->method('getRatesUrl')
            ->willReturn($url);

        $this->api->expects($this->once())
            ->method('sendRequest')
            ->with(
                $this->equalTo('get'),
                $this->equalTo($url . $currency)
            )
            ->willReturn($rates_stub);

        $rates = $this->provider->getRates($currency);
        $this->assertEquals($rates_stub->data, $rates);
    }

    public function testConvertCurrencySame()
    {
        $this->currency->method('getIso')
            ->willReturn(1);

        $this->amount_factory->method('create')
            ->willReturn($this->amount);

        $this->amount->method('getValue')
            ->willReturn('value');

        $amount = $this->provider->convertCurrency('1', $this->currency_alpha, 'USD', []);
        $this->assertSame('value', $amount);
    }

    public function testConvertCurrency()
    {
        $sum = '0.001';
        $currency_from = 'USD';
        $currency_to = 'XRP';
        $iso_from = 840;
        $iso_to = 1010;
        $rate = '264866406';
        $precision = 8;
        $result = '1234';

        $rates_stub = json_decode(
            '{"data":[{
                "from":{"alpha":"USD","iso":840},
                "to":{"alpha":"XRP","iso":1010},
                "rate":"264866406",
                "pow":8
            }]}'
        );

        $this->currency->method('getIso')
            ->will($this->onConsecutiveCalls($iso_from, $iso_to));

        $input_amount = $this->createMock(Amount::class);
        $rate_amount = $this->createMock(Amount::class);
        $result_amount = $this->createMock(Amount::class);

        $this->amount_factory->expects($this->exactly(2))
            ->method('create')
            ->withConsecutive(
                [$this->equalTo($sum), $this->equalTo($iso_from)],
                [$this->equalTo($rate), $this->isNull(), $this->equalTo($precision)]
            )
            ->will($this->onConsecutiveCalls($input_amount, $rate_amount));

        $this->currency->method('getPrecision')
            ->willReturn($precision);

        $input_amount->expects($this->once())
            ->method('convert')
            ->with(
                $this->equalTo($rate_amount),
                $this->equalTo($precision)
            )
            ->willReturn($result_amount);

        $result_amount->method('getValue')
            ->willReturn($result);

        $amount = $this->provider->convertCurrency($sum, $currency_from, $currency_to, $rates_stub->data);
        $this->assertSame($result, $amount);
    }

    public function testIncorrectRatesException()
    {
        $currency_to = $this->currency_alpha;

        $this->expectException(IncorrectRatesException::class);

        $this->currency->method('getIso')
            ->will($this->onConsecutiveCalls(1, 2));

        $this->amount_factory->method('create')
            ->willReturn($this->amount);

        $this->provider->convertCurrency('1', 'USD', $currency_to, []);
    }

    public function testAddMarkup()
    {
        $sum = '0.001';
        $iso = $this->currency_iso;
        $percent = 10;
        $result = '0.0011';

        $this->currency->method('getIso')
            ->willReturn($iso);

        $result_amount = $this->createMock(Amount::class);

        $this->amount_factory->expects($this->once())
            ->method('create')
            ->with(
                $this->equalTo($sum),
                $this->equalTo($iso)
            )
            ->willReturn($this->amount);

        $this->amount->expects($this->once())
            ->method('percentage')
            ->with(
                $this->equalTo($percent)
            )
            ->willReturn($result_amount);

        $result_amount->method('getValue')
            ->willReturn($result);

        $amount = $this->provider->addMarkup($sum, 'USD', $percent);
        $this->assertSame($result, $amount);
    }

    public function testCreateBill()
    {
        $url = 'url';
        $response_bill = '{ "data": { "id": "1" } }';
        $bill_stub = json_decode($response_bill);

        $currency = $this->currency_alpha;
        $iso = $this->currency_iso;

        $wallet_id = 1;
        $amount = '123';
        $precision = $this->currency_precision;
        $lifetime = 1200;
        $tracking_id = 'trackingId';
        $callback_url = 'callbackUrl';
        $success_url = 'successUrl';
        $error_url = 'errorUrl';
        $address = 'address';

        $params = [
            'form_params' => [
                'amount' => $amount,
                'wallet' => $wallet_id,
                'pow' => $precision,
                'lifetime' => $lifetime,
                'tracking_id' => $tracking_id,
                'callback_url' => $callback_url,
                'success_url' => $success_url,
                'error_url' => $error_url,
                'address' => $address
            ]
        ];

        $this->currency->method('getIso')
            ->willReturn($iso);

        $this->api->method('getNewBillUrl')
            ->willReturn($url);

        $this->amount_factory->method('create')
            ->willReturn($this->amount);

        $this->amount->method('getPowered')
            ->willReturn($amount);

        $this->amount->method('getPrecision')
            ->willReturn($precision);

        $this->api->expects($this->once())
            ->method('sendRequest')
            ->with(
                $this->equalTo('post'),
                $this->equalTo($url),
                $this->equalTo($params)
            )
            ->willReturn($bill_stub);

        $bill = $this->provider->createBill($wallet_id, $amount, $currency, $lifetime, $tracking_id, $callback_url, $success_url, $error_url, $address);
        $this->assertEquals($bill_stub->data, $bill);
    }

    public function testGetBills()
    {
        $url = 'url';
        $params = [
            'query' => [
                'currency' => 1
            ]
        ];
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

        $bills = $this->provider->getBills($params);
        $this->assertEquals($response, $bills);
    }

    public function testGetBill()
    {
        $url = 'url';

        $bill_id = 1;

        $response_bill = '{ "data": { "id": "' . $bill_id . '" } }';
        $bill_stub = json_decode($response_bill);

        $this->api->method('getBillsUrl')
            ->willReturn($url);

        $this->api->expects($this->once())
            ->method('sendRequest')
            ->with(
                $this->equalTo('get'),
                $this->equalTo($url)
            )
            ->willReturn($bill_stub);

        $bill = $this->provider->getBill($bill_id);
        $this->assertEquals($bill_stub->data, $bill);
    }

    public function testCreateWithdrawal()
    {
        $url = 'url';
        $response_withdrawal = '{ "data": { "id": "1" } }';
        $withdrawal_stub = json_decode($response_withdrawal);

        $currency = $this->currency_alpha;
        $iso = $this->currency_iso;

        $virtual_wallet_id = 1;
        $amount = '123';
        $address = 'address';
        $uniqueId = time();
        $tracking_id = 'trackingId';
        $pow = $this->currency_precision;
        $callback_url = 'callbackUrl';
        $message = 'message';
        $with_fee = false;

        $params = [
            'form_params' => [
                'amount' => $amount,
                'virtual_wallet_id' => $virtual_wallet_id,
                'address' => $address,
                'currency' => $iso,
                'unique_id' => $uniqueId,
                'tracking_id' => $tracking_id,
                'pow' => $pow,
                'callback_url' => $callback_url,
                'message' => $message,
                'with_fee' => $with_fee
            ]
        ];

        $this->currency->method('getIso')
            ->willReturn($iso);

        $this->api->method('getNewWithdrawalUrl')
            ->willReturn($url);

        $this->amount_factory->method('create')
            ->willReturn($this->amount);

        $this->amount->method('getPowered')
            ->willReturn($amount);

        $this->amount->method('getPrecision')
            ->willReturn($pow);

        $this->api->expects($this->once())
            ->method('sendRequest')
            ->with(
                $this->equalTo('post'),
                $this->equalTo($url),
                $this->equalTo($params)
            )
            ->willReturn($withdrawal_stub);

        $withdrawal = $this->provider->createWithdrawal($virtual_wallet_id, $amount, $currency, $address, $uniqueId, $tracking_id, $callback_url, $message, $with_fee);
        $this->assertEquals($withdrawal_stub->data, $withdrawal);
    }

    public function testGetWithdrawals()
    {
        $url = 'url';
        $params = [
            'query' => [
                'currency' => 1
            ]
        ];
        $response = [1, 2];

        $this->api->method('getWithdrawalsUrl')
            ->willReturn($url);

        $this->api->expects($this->once())
            ->method('sendRequest')
            ->with(
                $this->equalTo('get'),
                $this->equalTo($url)
            )
            ->willReturn($response);

        $withdrawals = $this->provider->getWithdrawals($params);
        $this->assertEquals($response, $withdrawals);
    }

    public function testGetWithdrawal()
    {
        $url = 'url';
        $withdrawal_id = 1;

        $response_withdrawal = '{ "data": { "id": "' . $withdrawal_id . '" } }';
        $withdrawal_stub = json_decode($response_withdrawal);

        $this->api->method('getWithdrawalsUrl')
            ->willReturn($url);

        $this->api->expects($this->once())
            ->method('sendRequest')
            ->with(
                $this->equalTo('get'),
                $this->equalTo($url)
            )
            ->willReturn($withdrawal_stub);

        $withdrawal = $this->provider->getWithdrawal($withdrawal_id);
        $this->assertEquals($withdrawal_stub->data, $withdrawal);
    }

    public function testGetTransfers()
    {
        $url = 'url';
        $params = [
            'query' => [
                'currency' => 1
            ]
        ];
        $response = [1, 2];

        $this->api->method('getTransfersUrl')
            ->willReturn($url);

        $this->api->expects($this->once())
            ->method('sendRequest')
            ->with(
                $this->equalTo('get'),
                $this->equalTo($url)
            )
            ->willReturn($response);

        $transfers = $this->provider->getTransfers($params);
        $this->assertEquals($response, $transfers);
    }

    public function testGetTransfer()
    {
        $url = 'url';

        $transfer_id = 1;

        $response_transfer = '{ "data": { "id": "' . $transfer_id . '" } }';
        $transfer_stub = json_decode($response_transfer);

        $this->api->method('getTransfersUrl')
            ->willReturn($url);

        $this->api->expects($this->once())
            ->method('sendRequest')
            ->with(
                $this->equalTo('get'),
                $this->equalTo($url)
            )
            ->willReturn($transfer_stub);

        $transfer = $this->provider->getTransfer($transfer_id);
        $this->assertEquals($transfer_stub->data, $transfer);
    }

    public function testGetTransactions()
    {
        $url = 'url';
        $params = [
            'query' => [
                'currency' => 1
            ]
        ];

        $response = [1, 2];

        $this->api->method('getTransactionsUrl')
            ->willReturn($url);

        $this->api->expects($this->once())
            ->method('sendRequest')
            ->with(
                $this->equalTo('get'),
                $this->equalTo($url)
            )
            ->willReturn($response);

        $transactions = $this->provider->getTransactions($params);
        $this->assertEquals($response, $transactions);
    }
    
    public function testGetTransaction()
    {
        $url = 'url';

        $transaction_id = 1;

        $response_transaction = '{ "data": { "id": "' . $transaction_id . '" } }';
        $transaction_stub = json_decode($response_transaction);

        $this->api->method('getTransactionsUrl')
            ->willReturn($url);

        $this->api->expects($this->once())
            ->method('sendRequest')
            ->with(
                $this->equalTo('get'),
                $this->equalTo($url)
            )
            ->willReturn($transaction_stub);

        $transaction = $this->provider->getTransaction($transaction_id);
        $this->assertEquals($transaction_stub->data, $transaction);
    }

    public function testGetVirtualWallets()
    {
        $url = 'url';
        $params = [
            'query' => [
                'currency' => 1
            ]
        ];

        $response = [1, 2];

        $this->api->method('getVirtualWalletsUrl')
            ->willReturn($url);

        $this->api->expects($this->once())
            ->method('sendRequest')
            ->with(
                $this->equalTo('get'),
                $this->equalTo($url)
            )
            ->willReturn($response);

        $virtual_wallets = $this->provider->getVirtualWallets($params);
        $this->assertEquals($response, $virtual_wallets);
    }

    public function testGetVirtualWallet()
    {
        $url = 'url';

        $virtual_wallet_id = 1;

        $response_virtual_wallet = '{ "data": { "id": "' . $virtual_wallet_id . '" } }';
        $virtual_wallet_stub = json_decode($response_virtual_wallet);

        $this->api->method('getVirtualWalletsUrl')
            ->willReturn($url);

        $this->api->expects($this->once())
            ->method('sendRequest')
            ->with(
                $this->equalTo('get'),
                $this->equalTo($url)
            )
            ->willReturn($virtual_wallet_stub);

        $virtual_wallet= $this->provider->getVirtualWallet($virtual_wallet_id);
        $this->assertEquals($virtual_wallet_stub->data, $virtual_wallet);
    }

    public function testVerifySign()
    {
        $sing_string = getenv('AUTH_KEY') . ":" . getenv('AUTH_SECRET') . ":" . $this->sign_time;

        $this->api->method('genSignString')
            ->willReturn($sing_string);

        $check_sign = $this->provider->verifySign($this->sign_time, $this->sign_hash);
        $this->assertTrue($check_sign);
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
}
