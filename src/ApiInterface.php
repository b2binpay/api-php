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
     * @param array|null $params
     * @return mixed
     */
    public function sendRequest(string $method, string $url, array $params = null);

    /**
     * @return string
     */
    public function genAuthBasic(): string;

    /**
     * @return string
     */
    public function getAccessToken(): string;

    /**
     * @param int|null $bill
     * @return string
     */
    public function getBillsUrl(int $bill = null): string;

    /**
     * @param int $iso
     * @return string
     */
    public function getNewBillUrl(int $iso): string;

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
}
