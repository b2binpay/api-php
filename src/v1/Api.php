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
    const GW_TEST = 'https://paysystemtest.b2broker.info';

    const URI_LOGIN = '/api/login';
    const URI_BILLS = '/api/v1/pay/bills';
    const URI_WALLETS = '/api/v1/pay/wallets';
    const URI_DEPOSIT = '/api/v1/rates/deposit/';
    const URI_WITHDRAW = '/api/v1/rates/withdraw/';

    /**
     * @var array List of nodes
     */
    private static $node = [
        1000 => 'https://btc.b2binpay.com',
        1002 => 'https://eth.b2binpay.com',
        1003 => 'https://ltc.b2binpay.com',
        1005 => 'https://dash.b2binpay.com',
        1006 => 'https://bch.b2binpay.com',
        1007 => 'https://xmr.b2binpay.com',
        1010 => 'https://xrp.b2binpay.com',
        1012 => 'https://nem.b2binpay.com',
        2000 => 'https://eth.b2binpay.com',
        2005 => 'https://omni.b2binpay.com',
        2006 => 'https://eth.b2binpay.com',
        2014 => 'https://neo.b2binpay.com'
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
     * @param array|null $params
     * @return mixed
     */
    public function sendRequest(string $method, string $url, array $params = null)
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
     * @param int $iso
     * @return string
     * @throws UnknownValueException
     */
    public static function getNode(int $iso): string
    {
        if (!array_key_exists($iso, self::$node)) {
            throw new UnknownValueException($iso);
        }

        return self::$node[$iso];
    }

    /**
     * @param int $iso
     * @return string
     */
    public function getNewBillUrl(int $iso): string
    {
        $gateway = ($this->testing) ? self::GW_TEST : $this->getNode($iso);
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
     * @param string $rateType = 'deposit' or 'withdraw'
     * @return string
     */
    public function getRatesUrl(string $rateType = 'deposit'): string
    {
        $url = $this->getGateway();
        $url .= ('deposit' === $rateType) ? self::URI_DEPOSIT : self::URI_WITHDRAW;

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
