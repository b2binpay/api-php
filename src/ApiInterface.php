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
     * @param string|null $rate_type = 'deposit' or 'withdraw'
     * @return string
     */
    public function getRatesUrl(string $rate_type = 'deposit'): string;

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
     * @param int|null $transaction_id
     * @return string
     */
    public function getTransactionsUrl(int $transaction_id = null): string;

    /**
     * @param int|null $virtual_wallet_id
     * @return string
     */
    public function getVirtualWalletsUrl(int $virtual_wallet_id = null): string;

    /**
     * @return string
     */
    public function getNewWithdrawalUrl(): string;

    /**
     * @param int|null $withdrawal_id
     * @return string
     */
    public function getWithdrawalsUrl(int $withdrawal_id = null): string;

    /**
     * @param int|null $transfer_id
     * @return string
     */
    public function getTransfersUrl(int $transfer_id = null): string;

    /**
     * @param string $time
     * @return string
     */
    public function genSignString(string $time): string;
}
