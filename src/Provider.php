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
    )
    {
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
     * @param string $rate_type = 'deposit' or 'withdraw'
     * @return mixed Rates
     * @throws B2BinpayException
     */
    public function getRates(string $currency = 'USD', string $rate_type = 'deposit')
    {
        $url = $this->api->getRatesUrl($rate_type) . strtolower($currency);

        $response = $this->api->sendRequest('get', $url);

        return $response->data;
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
    ): string
    {
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
            }
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
     * @param int $walletId
     * @param string $amount
     * @param string $currency
     * @param int $lifetime
     * @param string|null $trackingId
     * @param string|null $callbackUrl
     * @param string|null $successUrl
     * @param string|null $errorUrl
     * @param string|null $address
     * @return mixed Bill
     */
    public function createBill(
        int $walletId,
        string $amount,
        string $currency,
        int $lifetime,
        string $trackingId = null,
        string $callbackUrl = null,
        string $successUrl = null,
        string $errorUrl = null,
        string $address = null
    )
    {
        $iso = $this->currency->getIso($currency);
        $url = $this->api->getNewBillUrl($currency);

        $amountFactory = $this->amountFactory->create($amount, $iso);

        $params = [
            'form_params' => [
                'amount' => $amountFactory->getPowered(),
                'wallet' => $walletId,
                'pow' => $amountFactory->getPrecision(),
                'lifetime' => $lifetime,
                'tracking_id' => $trackingId,
                'callback_url' => $callbackUrl,
                'success_url' => $successUrl,
                'error_url' => $errorUrl,
                'address' => $address
            ]
        ];

        $response = $this->api->sendRequest('post', $url, $params);

        return $response->data;
    }

    /**
     * @param array $params
     * @return mixed Bills list
     * @throws B2BinpayException
     */
    public function getBills(array $params = [])
    {
        $url = $this->api->getBillsUrl(null, $params);

        return $this->api->sendRequest('get', $url, $params);
    }

    /**
     * @param int $billId
     * @return mixed Bill
     * @throws B2BinpayException
     */
    public function getBill(int $billId)
    {
        $url = $this->api->getBillsUrl($billId);

        $response = $this->api->sendRequest('get', $url);

        return $response->data;
    }

    /**
     * @param array $params
     * @return mixed Transactions list
     * @throws B2BinpayException
     */
    public function getTransactions(array $params = [])
    {
        $url = $this->api->getTransactionsUrl(null, $params);

        return $this->api->sendRequest('get', $url, $params);
    }

    /**
     * @param int $transaction_id
     * @return mixed Transaction
     * @throws B2BinpayException
     */
    public function getTransaction(int $transaction_id)
    {
        $url = $this->api->getTransactionsUrl($transaction_id);

        $response = $this->api->sendRequest('get', $url);

        return $response->data;
    }

    /**
     * @param array $params
     * @return mixed VirtualWallets list
     * @throws B2BinpayException
     */
    public function getVirtualWallets(array $params = [])
    {
        $url = $this->api->getVirtualWalletsUrl(null, $params);

        return $this->api->sendRequest('get', $url, $params);
    }

    /**
     * @param int $virtual_wallet_id
     * @return mixed VirtualWallet
     * @throws B2BinpayException
     */
    public function getVirtualWallet(int $virtual_wallet_id)
    {
        $url = $this->api->getVirtualWalletsUrl($virtual_wallet_id);

        $response = $this->api->sendRequest('get', $url);

        return $response->data;
    }

    /**
     * @param int $virtual_wallet_id
     * @param string $amount
     * @param string $currency
     * @param string $address
     * @param int $uniqueId
     * @param string|null $trackingId
     * @param string|null $callbackUrl
     * @param string|null $message
     * @param boolean $with_fee
     * @return mixed Withdrawal
     */
    public function createWithdrawal(
        int $virtual_wallet_id,
        string $amount,
        string $currency,
        string $address,
        int $uniqueId,
        string $trackingId = null,
        string $callbackUrl = null,
        string $message = null,
        bool $with_fee = false
    )
    {
        $iso = $this->currency->getIso($currency);
        $url = $this->api->getNewWithdrawalUrl();

        $amountFactory = $this->amountFactory->create($amount, $iso);

        $params = [
            'form_params' => [
                'amount' => $amountFactory->getPowered(),
                'virtual_wallet_id' => $virtual_wallet_id,
                'address' => $address,
                'currency' => $iso,
                'unique_id' => $uniqueId,
                'tracking_id' => $trackingId,
                'pow' => $amountFactory->getPrecision(),
                'callback_url' => $callbackUrl,
                'message' => $message,
                'with_fee' => $with_fee
            ]
        ];

        $response = $this->api->sendRequest('post', $url, $params);

        return $response->data;
    }

    /**
     * @param array $params
     * @return mixed Withdrawals list
     * @throws B2BinpayException
     */
    public function getWithdrawals(array $params = [])
    {
        $url = $this->api->getWithdrawalsUrl(null, $params);

        return $this->api->sendRequest('get', $url, $params);
    }

    /**
     * @param int $withdrawal_id
     * @return mixed Withdrawal
     * @throws B2BinpayException
     */
    public function getWithdrawal(int $withdrawal_id)
    {
        $url = $this->api->getWithdrawalsUrl($withdrawal_id);

        $response = $this->api->sendRequest('get', $url);

        return $response->data;
    }

    /**
     * @param array $params
     * @return mixed Transfers list
     * @throws B2BinpayException
     */
    public function getTransfers(array $params = [])
    {
        $url = $this->api->getTransfersUrl(null, $params);

        return $this->api->sendRequest('get', $url, $params);
    }

    /**
     * @param int $transfer_id
     * @return mixed Transfer
     * @throws B2BinpayException
     */
    public function getTransfer(int $transfer_id)
    {
        $url = $this->api->getTransfersUrl($transfer_id);

        $response = $this->api->sendRequest('get', $url);

        return $response->data;
    }

    /**
     * @param string $time
     * @param string $sign
     * @return boolean
     */
    public function verifySign(string $time, string $sign): bool
    {
        $verify = $this->api->genSignString($time);

        return password_verify($verify, $sign);
    }

}
