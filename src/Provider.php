<?php
declare(strict_types=1);

namespace B2Binpay;

use B2Binpay\v1\Api;
use B2Binpay\Exception\B2BinpayException;
use B2Binpay\Exception\IncorrectRatesException;
use GuzzleHttp\Client;

/**
 * B2BinPay payment provider
 *
 * @package B2Binpay
 */
class Provider
{
    /**
     * @var ApiInterface
     */
    private $api;

    /**
     * @var Currency
     */
    private $currency;

    /**
     * @var AmountFactory
     */
    private $amountFactory;

    /**
     * @param string $authKey
     * @param string $authSecret
     * @param bool|false $testing
     * @param Client|null $client
     * @param Currency|null $currency
     * @param AmountFactory|null $amountFactory
     * @param ApiInterface|null $api
     */
    public function __construct(
        string $authKey,
        string $authSecret,
        bool $testing = false,
        Client $client = null,
        Currency $currency = null,
        AmountFactory $amountFactory = null,
        ApiInterface $api = null
    ) {
        $this->currency = $currency ?? new Currency();
        $this->amountFactory = $amountFactory ?? new AmountFactory($this->currency);

        $request = ($client) ? new Request($client) : null;

        $this->api = $api ?? new Api(
            $authKey,
            $authSecret,
            $request,
            $testing
        );
    }

    /**
     * @return string
     */
    public function getAuthorization(): string
    {
        return 'Basic ' . $this->api->genAuthBasic();
    }

    /**
     * @return string
     */
    public function getAuthToken(): string
    {
        return $this->api->getAccessToken();
    }

    /**
     * @param string $currency
     * @return mixed Rates
     * @throws B2BinpayException
     */
    public function getRates(string $currency)
    {
        $iso = $this->currency->getIso($currency);
        $alpha = $this->currency->getAlpha($iso);
        $url = $this->api->getRatesUrl() . strtolower($alpha);

        $response = $this->api->sendRequest('get', $url);

        return $response;
    }

    /**
     * @return mixed Wallets List
     * @throws B2BinpayException
     */
    public function getWallets()
    {
        $url = $this->api->getWalletsUrl();

        $response = $this->api->sendRequest('get', $url);

        return $response;
    }

    /**
     * @param int $wallet
     * @return mixed Wallet
     * @throws B2BinpayException
     */
    public function getWallet(int $wallet)
    {
        $url = $this->api->getWalletsUrl($wallet);

        $response = $this->api->sendRequest('get', $url);

        return $response;
    }

    /**
     * @param string $sum
     * @param string $currencyFrom
     * @param string $currencyTo
     * @param array|null $rates
     * @return string
     * @throws IncorrectRatesException
     */
    public function convertCurrency(
        string $sum,
        string $currencyFrom,
        string $currencyTo,
        array $rates = null
    ): string {
        $isoFrom = $this->currency->getIso($currencyFrom);
        $isoTo = $this->currency->getIso($currencyTo);

        $input = $this->amountFactory->create($sum, $isoFrom);

        if ($isoFrom === $isoTo) {
            return $input->getValue();
        }

        $rates = $rates ?? $this->getRates($currencyFrom);

        $rate = array_reduce(
            $rates,
            function ($carry, $item) use ($isoTo) {
                if ($item->to->iso === $isoTo) {
                    $carry = $this->amountFactory->create($item->rate, null, $item->pow);
                }
                return $carry;
            },
            array()
        );

        if (empty($rate)) {
            throw new IncorrectRatesException("Can't get rates to convert from $isoFrom to $isoTo");
        }

        $precision = $this->currency->getPrecision($isoTo);

        return $input->convert($rate, $precision)->getValue();
    }

    /**
     * @param string $sum
     * @param string $currency
     * @param int $percent
     * @return string
     */
    public function addMarkup(string $sum, string $currency, int $percent): string
    {
        $iso = $this->currency->getIso($currency);

        $amount = $this->amountFactory->create($sum, $iso);

        return $amount->percentage($percent)->getValue();
    }

    /**
     * @param int $wallet
     * @param string $sum
     * @param string $currency
     * @param int $lifetime
     * @param string|null $trackingId
     * @param string|null $callbackUrl
     * @return mixed Bill
     */
    public function createBill(
        int $wallet,
        string $sum,
        string $currency,
        int $lifetime,
        string $trackingId = null,
        string $callbackUrl = null
    ) {
        $iso = $this->currency->getIso($currency);
        $url = $this->api->getNewBillUrl($iso);

        $amount = $this->amountFactory->create($sum, $iso);

        $params = [
            'amount' => $amount->getPowed(),
            'wallet' => $wallet,
            'pow' => $amount->getPrecision(),
            'lifetime' => $lifetime,
            'tracking_id' => $trackingId,
            'callback_url' => $callbackUrl
        ];

        $response = $this->api->sendRequest('post', $url, $params);

        return $response;
    }

    /**
     * @param int $wallet
     * @return mixed Bills list
     * @throws B2BinpayException
     */
    public function getBills(int $wallet)
    {
        $url = $this->api->getBillsUrl($wallet);

        $response = $this->api->sendRequest('get', $url);

        return $response;
    }

    /**
     * @param int $bill
     * @return mixed Bill
     * @throws B2BinpayException
     */
    public function getBill(int $bill)
    {
        $url = $this->api->getBillsUrl($bill);

        $response = $this->api->sendRequest('get', $url);

        return $response;
    }
}
