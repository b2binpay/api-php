<?php
declare(strict_types=1);

namespace B2Binpay\v1;

use B2Binpay\ApiInterface;
use B2Binpay\Request;
use B2Binpay\Exception\UpdateTokenException;
use B2Binpay\Exception\UnknownValueException;

/**
 * B2BinPay API Client v1 Implementation
 *
 * @package B2Binpay
 */
class Api implements ApiInterface
{
    const GW_PRODUCTION = 'https://gw.b2binpay.com';
    const GW_TEST = 'https://gw-test.b2binpay.com';

    const URI_LOGIN = '/api/login';
    const URI_BILLS = '/api/v1/pay/bills';
    const URI_WALLETS = '/api/v1/pay/wallets';
    const URI_TRANSACTIONS = '/api/v1/pay/transactions';
    const URI_RATES_DEPOSIT = '/api/v1/rates/deposit/';
    const URI_RATES_WITHDRAW = '/api/v1/rates/withdraw/';
    const URI_VIRTUAL_WALLETS = '/api/v1/virtualwallets/wallets';
    const URI_WITHDRAWS = '/api/v1/virtualwallets/withdraws';
    const URI_TRANSFERS = '/api/v1/virtualwallets/transfers';

    /**
     * @var array List of nodes
     */
    private static $node = [
        'ADA' => 'https://ada.b2binpay.com',
        'BCH' => 'https://bch.b2binpay.com',
        'BNB' => 'https://bnb.b2binpay.com',
        'BTC' => 'https://btc.b2binpay.com',
        'BUSD-ETH' => 'https://eth.b2binpay.com',
        'DAI-ETH' => 'https://eth.b2binpay.com',
        'DASH' => 'https://dash.b2binpay.com',
        'DOGE' => 'https://doge.b2binpay.com',
        'EOS' => 'https://eos.b2binpay.com',
        'ETH' => 'https://eth.b2binpay.com',
        'GUSD-ETH' => 'https://eth.b2binpay.com',
        'LTC' => 'https://ltc.b2binpay.com',
        'NEO' => 'https://neo.b2binpay.com',
        'PAX-ETH' => 'https://eth.b2binpay.com',
        'TRX' => 'https://tron.b2binpay.com',
        'TUSD-ETH' => 'https://eth.b2binpay.com',
        'USDC-ETH' => 'https://eth.b2binpay.com',
        'USDT-ETH' => 'https://eth.b2binpay.com',
        'USDT-OMNI' => 'https://omni.b2binpay.com',
        'XEM' => 'https://nem.b2binpay.com',
        'XLM' => 'https://xlm.b2binpay.com',
        'XMR' => 'https://xmr.b2binpay.com',
        'XRP' => 'https://xrp.b2binpay.com',
        'ZEC' => 'https://zec.b2binpay.com',
    ];

    /**
     * @var string
     */
    private $authKey;

    /**
     * @var string
     */
    private $authSecret;

    /**
     * @var string
     */
    private $accessToken;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var bool
     */
    private $testing;

    /**
     * @param string $authKey
     * @param string $authSecret
     * @param Request|null $request
     * @param bool|false $testing
     */
    public function __construct(
        string $authKey,
        string $authSecret,
        Request $request = null,
        bool $testing = false
    ) {
        $this->authKey = $authKey;
        $this->authSecret = $authSecret;
        $this->request = $request ?? new Request();
        $this->testing = $testing;
    }

    /**
     * @param string $method
     * @param string $url
     * @param array $params
     * @return mixed
     */
    public function sendRequest(string $method, string $url, array $params = [])
    {
        $token = $this->getAccessToken();

        try {
            $result = $this->request->send($token, $method, $url, $params);
        } catch (UpdateTokenException $e) {
            $this->accessToken = null;
            $token = $this->getAccessToken();
            $result = $this->request->send($token, $method, $url, $params);
        }

        return $result;
    }

    /**
     * @return string
     */
    private function getGateway(): string
    {
        return ($this->testing) ? self::GW_TEST : self::GW_PRODUCTION;
    }

    /**
     * @param string $currency
     * @return string
     * @throws UnknownValueException
     */
    public static function getNode(string $currency): string
    {
        if (!array_key_exists($currency, self::$node)) {
            throw new UnknownValueException($currency);
        }

        return self::$node[$currency];
    }

    /**
     * @param string $rate_type = 'deposit' or 'withdraw'
     * @return string
     */
    public function getRatesUrl(string $rate_type = 'deposit'): string
    {
        $url = $this->getGateway();
        $url .= ('deposit' === $rate_type) ? self::URI_RATES_DEPOSIT : self::URI_RATES_WITHDRAW;

        return $url;
    }

    /**
     * @param int|null $wallet
     * @return string
     */
    public function getWalletsUrl(int $wallet = null): string
    {
        $uri = self::URI_WALLETS;
        if (!empty($wallet)) {
            $uri .= '/' . $wallet;
        }

        return $this->getGateway() . $uri;
    }

    /**
     * @param string $currency
     * @return string
     */
    public function getNewBillUrl(string $currency): string
    {
        $gateway = ($this->testing) ? self::GW_TEST : $this->getNode($currency);
        $uri = self::URI_BILLS;

        return $gateway . $uri;
    }

    /**
     * @param int|null $bill
     * @return string
     */
    public function getBillsUrl(int $bill = null): string
    {
        $gateway = ($this->testing) ? self::GW_TEST : self::GW_PRODUCTION;
        $uri = self::URI_BILLS;

        if (!empty($bill)) {
            $uri .= '/' . $bill;
        }

        return $gateway . $uri;
    }

    /**
     * @param int|null $transaction_id
     * @return string
     */
    public function getTransactionsUrl(int $transaction_id = null): string
    {
        $uri = self::URI_TRANSACTIONS;
        if (!empty($transaction_id)) {
            $uri .= '/' . $transaction_id;
        }

        return $this->getGateway() . $uri;
    }

    /**
     * @param int|null $virtual_wallet_id
     * @return string
     */
    public function getVirtualWalletsUrl(int $virtual_wallet_id = null): string
    {
        $uri = self::URI_VIRTUAL_WALLETS;
        if (!empty($virtual_wallet_id)) {
            $uri .= '/' . $virtual_wallet_id;
        }

        return $this->getGateway() . $uri;
    }

    /**
     * @return string
     */
    public function getNewWithdrawalUrl(): string
    {
        $uri = self::URI_WITHDRAWS;

        return $this->getGateway() . $uri;
    }

    /**
     * @param int|null $withdrawal_id
     * @return string
     */
    public function getWithdrawalsUrl(int $withdrawal_id = null): string
    {
        $uri = self::URI_WITHDRAWS;
        if (!empty($withdrawal_id)) {
            $uri .= '/' . $withdrawal_id;
        }

        return $this->getGateway() . $uri;
    }

    /**
     * @param int|null $transfer_id
     * @return string
     */
    public function getTransfersUrl(int $transfer_id = null): string
    {
        $uri = self::URI_TRANSFERS;
        if (!empty($transfer_id)) {
            $uri .= '/' . $transfer_id;
        }

        return $this->getGateway() . $uri;
    }

    /**
     * @return string
     */
    public function genAuthBasic(): string
    {
        return (string)base64_encode("$this->authKey:$this->authSecret");
    }

    /**
     * @return string
     */
    public function getAccessToken(): string
    {
        if (empty($this->accessToken)) {
            $url = $this->getGateway() . self::URI_LOGIN;
            $this->accessToken = $this->request->token($this->genAuthBasic(), $url);
        }
        return $this->accessToken;
    }

    /**
     * @param string $time
     * @return string
     */
    public function genSignString(string $time): string
    {
        return $this->authKey . ':' . $this->authSecret . ':' . $time;
    }

    /**
     * @param string $accessToken
     */
    public function setAccessToken(string $accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * @param bool $testing
     */
    public function setTesting(bool $testing)
    {
        $this->testing = $testing;
    }
}
