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
    protected $amountFactory;

    /**
     * @var Amount | MockObject
     */
    protected $amount;

    /**
     * @var ApiInterface | MockObject
     */
    protected $api;

    private $currencyIso;
    private $currencyAlpha;
    private $currencyPrecision;

    private $signTime;
    private $signHash;

    public function setUp(): void
    {
        $this->currency = $this->createMock(Currency::class);
        $this->amountFactory = $this->createMock(AmountFactory::class);
        $this->amount = $this->createMock(Amount::class);
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

        $this->currencyIso = (int)getenv('CURRENCY_ISO');
        $this->currencyAlpha = getenv('CURRENCY_ALPHA');
        $this->currencyPrecision = (int)getenv('CURRENCY_PRECISION');

        $this->signTime = getenv('SIGN_TIME');
        $this->signHash = getenv('SIGN_HASH');
    }

    public function tearDown(): void
    {
        $this->provider = null;
        $this->currency = null;
        $this->amountFactory = null;
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
        $currency = strtolower($this->currencyAlpha);

        $responseRates = '{ "data": [{ "rate": "deposit" }] }';
        $ratesStub = json_decode($responseRates);

        $this->api->method('getRatesUrl')
            ->willReturn($url);

        $this->api->expects($this->once())
            ->method('sendRequest')
            ->with(
                $this->equalTo('get'),
                $this->equalTo($url . $currency)
            )
            ->willReturn($ratesStub);

        $rates = $this->provider->getRates($currency);
        $this->assertEquals($ratesStub->data, $rates);
    }

    public function testConvertCurrencySame()
    {
        $this->currency->method('getIso')
            ->willReturn(1);

        $this->amountFactory->method('create')
            ->willReturn($this->amount);

        $this->amount->method('getValue')
            ->willReturn('value');

        $amount = $this->provider->convertCurrency('1', $this->currencyAlpha, 'USD', []);
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

    public function testIncorrectRatesException()
    {
        $currencyTo = $this->currencyAlpha;

        $this->expectException(IncorrectRatesException::class);

        $this->currency->method('getIso')
            ->will($this->onConsecutiveCalls(1, 2));

        $this->amountFactory->method('create')
            ->willReturn($this->amount);

        $this->provider->convertCurrency('1', 'USD', $currencyTo, []);
    }

    public function testAddMarkup()
    {
        $sum = '0.001';
        $iso = $this->currencyIso;
        $percent = 10;
        $result = '0.0011';

        $this->currency->method('getIso')
            ->willReturn($iso);

        $resultAmount = $this->createMock(Amount::class);

        $this->amountFactory->expects($this->once())
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
            ->willReturn($resultAmount);

        $resultAmount->method('getValue')
            ->willReturn($result);

        $amount = $this->provider->addMarkup($sum, 'USD', $percent);
        $this->assertSame($result, $amount);
    }

    public function testCreateBill()
    {
        $url = 'url';
        $responseBill = '{ "data": { "id": "1" } }';
        $billStub = json_decode($responseBill);

        $currency = $this->currencyAlpha;
        $iso = $this->currencyIso;

        $walletId = 1;
        $amount = '123';
        $precision = $this->currencyPrecision;
        $lifetime = 1200;
        $trackingId = 'trackingId';
        $callbackUrl = 'callbackUrl';
        $successUrl = 'successUrl';
        $errorUrl = 'errorUrl';
        $address = 'address';

        $params = [
            'form_params' => [
                'amount' => $amount,
                'wallet' => $walletId,
                'pow' => $precision,
                'lifetime' => $lifetime,
                'tracking_id' => $trackingId,
                'callback_url' => $callbackUrl,
                'success_url' => $successUrl,
                'error_url' => $errorUrl,
                'address' => $address
            ]
        ];

        $this->currency->method('getIso')
            ->willReturn($iso);

        $this->api->method('getNewBillUrl')
            ->willReturn($url);

        $this->amountFactory->method('create')
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
            ->willReturn($billStub);

        $bill = $this->provider->createBill($walletId, $amount, $currency, $lifetime, $trackingId, $callbackUrl, $successUrl, $errorUrl, $address);
        $this->assertEquals($billStub->data, $bill);
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

        $billId = 1;

        $responseBill = '{ "data": { "id": "' . $billId . '" } }';
        $billStub = json_decode($responseBill);

        $this->api->method('getBillsUrl')
            ->willReturn($url);

        $this->api->expects($this->once())
            ->method('sendRequest')
            ->with(
                $this->equalTo('get'),
                $this->equalTo($url)
            )
            ->willReturn($billStub);

        $bill = $this->provider->getBill($billId);
        $this->assertEquals($billStub->data, $bill);
    }

    public function testCreateWithdrawal()
    {
        $url = 'url';
        $responseWithdrawal = '{ "data": { "id": "1" } }';
        $withdrawalStub = json_decode($responseWithdrawal);

        $currency = $this->currencyAlpha;
        $iso = $this->currencyIso;

        $virtualWalletId = 1;
        $amount = '123';
        $address = 'address';
        $uniqueId = time();
        $trackingId = 'trackingId';
        $pow = $this->currencyPrecision;
        $callbackUrl = 'callbackUrl';
        $message = 'message';
        $with_fee = false;

        $params = [
            'form_params' => [
                'amount' => $amount,
                'virtual_wallet_id' => $virtualWalletId,
                'address' => $address,
                'currency' => $iso,
                'unique_id' => $uniqueId,
                'tracking_id' => $trackingId,
                'pow' => $pow,
                'callback_url' => $callbackUrl,
                'message' => $message,
                'with_fee' => $with_fee
            ]
        ];

        $this->currency->method('getIso')
            ->willReturn($iso);

        $this->api->method('getNewWithdrawalUrl')
            ->willReturn($url);

        $this->amountFactory->method('create')
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
            ->willReturn($withdrawalStub);

        $withdrawal = $this->provider->createWithdrawal($virtualWalletId, $amount, $currency, $address, $uniqueId, $trackingId, $callbackUrl, $message, $with_fee);
        $this->assertEquals($withdrawalStub->data, $withdrawal);
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
        $withdrawalId = 1;

        $responseWithdrawal = '{ "data": { "id": "' . $withdrawalId . '" } }';
        $withdrawalStub = json_decode($responseWithdrawal);

        $this->api->method('getWithdrawalsUrl')
            ->willReturn($url);

        $this->api->expects($this->once())
            ->method('sendRequest')
            ->with(
                $this->equalTo('get'),
                $this->equalTo($url)
            )
            ->willReturn($withdrawalStub);

        $withdrawal = $this->provider->getWithdrawal($withdrawalId);
        $this->assertEquals($withdrawalStub->data, $withdrawal);
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

        $transferId = 1;

        $responseTransfer = '{ "data": { "id": "' . $transferId . '" } }';
        $transferStub = json_decode($responseTransfer);

        $this->api->method('getTransfersUrl')
            ->willReturn($url);

        $this->api->expects($this->once())
            ->method('sendRequest')
            ->with(
                $this->equalTo('get'),
                $this->equalTo($url)
            )
            ->willReturn($transferStub);

        $transfer = $this->provider->getTransfer($transferId);
        $this->assertEquals($transferStub->data, $transfer);
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

        $transactionId = 1;

        $responseTransaction = '{ "data": { "id": "' . $transactionId . '" } }';
        $transactionStub = json_decode($responseTransaction);

        $this->api->method('getTransactionsUrl')
            ->willReturn($url);

        $this->api->expects($this->once())
            ->method('sendRequest')
            ->with(
                $this->equalTo('get'),
                $this->equalTo($url)
            )
            ->willReturn($transactionStub);

        $transaction = $this->provider->getTransaction($transactionId);
        $this->assertEquals($transactionStub->data, $transaction);
    }

    public function testVerifySign()
    {
        $singString = getenv('AUTH_KEY').":".getenv('AUTH_SECRET').":".$this->signTime;

        $this->api->method('genSignString')
            ->willReturn($singString);

        $checkSign = $this->provider->verifySign($this->signTime, $this->signHash);
        $this->assertTrue($checkSign);
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
