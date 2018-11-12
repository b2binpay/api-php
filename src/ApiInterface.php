<?php
declare(strict_types=1);

namespace B2Binpay;

use B2Binpay\Exception\ConnectionErrorException;
use B2Binpay\Exception\EmptyResponseException;
use B2Binpay\Exception\ServerApiException;
use B2Binpay\Exception\UnknownValueException;

/**
 * B2BinPAY API Client Interface
 */
interface ApiInterface
{
    /**
     * @param string $method
     * @param string $url
     * @param array|null $params
     * @return mixed
     * @throws ConnectionErrorException
     * @throws ServerApiException
     * @throws EmptyResponseException
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
