<?php
declare(strict_types=1);

namespace B2Binpay;

/**
 * B2BinPay API Client Interface
 *
 * @package B2Binpay
 */
interface ApiInterface
{
    /**
     * @param string $method
     * @param string $url
     * @param array $params
     * @return mixed
     */
    public function sendRequest(string $method, string $url, array $params = []);

    /**
     * @return string
     */
    public function genAuthBasic(): string;

    /**
     * @return string
     */
    public function getAccessToken(): string;

    /**
     * @param string|null $rateType = 'deposit' or 'withdraw'
     * @return string
     */
    public function getRatesUrl(string $rateType = 'deposit'): string;

    /**
     * @param int|null $wallet
     * @return string
     */
    public function getWalletsUrl(int $wallet = null): string;

    /**
     * @param int|null $bill
     * @return string
     */
    public function getBillsUrl(int $bill = null): string;

    /**
     * @param string $currency
     * @return string
     */
    public function getNewBillUrl(string $currency): string;

    /**
     * @param int|null $transactionId
     * @return string
     */
    public function getTransactionsUrl(int $transactionId = null): string;

    /**
     * @param int|null $virtualWalletId
     * @return string
     */
    public function getVirtualWalletsUrl(int $virtualWalletId = null): string;

    /**
     * @return string
     */
    public function getNewWithdrawalUrl(): string;

    /**
     * @param int|null $withdrawalId
     * @return string
     */
    public function getWithdrawalsUrl(int $withdrawalId = null): string;

    /**
     * @param int|null $transferlId
     * @return string
     */
    public function getTransfersUrl(int $transferlId = null): string;

    /**
     * @param string $time
     * @return string
     */
    public function genSignString(string $time): string;
}
