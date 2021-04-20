<?php
require_once __DIR__ . '/config.php';

// Create B2Binpay Currency object
$currency = new B2Binpay\Currency();

$withdraws_param = [
    'query' => [
        'filter' => [
            'virtual_wallet_id' => 45, // (int) Get all withdraws on the virtual wallet id 45
            // 'address' => '5f7af8aed5fa7a9df2255ad642b923d95503b9a7958d8' // (string) Blockchain address withdraw
            'currency' => $currency->getIso('BTC'), // (int) ISO code currency
            // 'status' => 2, // (int) See in list of withdraws statuses
            // 'amount' => 9.1, // (float) Amount from which the deposit was received
            // 'transaction' => 'NWY1YjQ0NDExZGI4YmUwMmUyN2UwNGZkZmY5NjdiYTdkNzZmYjI0YjgwNjlkIzAuMDEwNjY1NjAwMDAwMDAwMDAw', // (string) Blockchain hash transactions
            // 'tracking_id' => '202010010001', // (string) Track for withdraw tracking
            // 'created' => '2020-10-01 00:00:00,2020-10-01 23:59:59' // (string) Withdraw creation date or between dates
        ],
        /*
         * For sorting can be use values:
         * gt, more (>)
         * gte,more or equal (>=)
         * lt, less (<)
         * lte, less or equal (<=)
         * bt, between
         */
        'filter_type' => [
            // 'amount' => 'gte', // Withdraws will be filter whose amount is more than or equal to $withdrawsParam['query]['filter']['amount'];
            // 'created' => 'bt', // Date of creation between $withdrawsParam['query]['filter']['created']
        ],
        'sort' => '-id', // Reverse sorting by ID field, default: id
        'pagesize' => 50, // Maximum results per page 250, default: 20
        'page' => 1
    ]
];

// Create B2Binpay Provider object
$provider = new B2Binpay\Provider(
    getAuthKey(),
    getAuthSecret(),
    true // sandbox
);

// Get withdraws
$withdraws = $provider->getWithdrawals($withdraws_param);
// Or get withdraw by id
// $withdrawsId = 14033;
// $withdraw = $provider->getWithdrawal($withdrawsId);

echo '<pre>';
print_r($withdraws);
echo '</pre>';

/**
 * @example
 *
 * stdClass Object
 * (
 * [data] => Array
 * (
 * [0] => stdClass Object
 * (
 * [id] => 129
 * [virtual_wallet_id] => 45
 * [with_fee] => 1
 * [created] => 2019-06-11 10:40:19
 * [address] => 5cff8013930f62bce32ed409f5ebcee2a7b417ad9beed
 * [message] =>
 * [amount] => 100000
 * [fee] => 6000000
 * [pow] => 8
 * [status] => 2
 * [transaction] => trx5cff851395c57f6d9e459b9fbf6dd26c4f7d621adec1d
 * [tracking_id] =>
 * [unique_id] => 12345
 * [callback_url] =>
 * [currency] => stdClass Object
 * (
 * [iso] => 1000
 * [alpha] => BTC
 * )
 * )
 * [1] => stdClass Object
 * (
 * [id] => 113
 * [virtual_wallet_id] => 45
 * [with_fee] => 0
 * [created] => 2019-05-29 16:22:38
 * [address] => dfhluuuk
 * [message] =>
 * [amount] => 100000000
 * [fee] => 1000000
 * [pow] => 8
 * [status] => 2
 * [transaction] => trx5ceeb221d137ac1502ae5a4d514baec129f72948c266e
 * [tracking_id] =>
 * [unique_id] => 2543
 * [callback_url] =>
 * [currency] => stdClass Object
 * (
 * [iso] => 1000
 * [alpha] => BTC
 * )
 * )
 * )
 * [links] => stdClass Object
 * (
 * [first] => https://gw-test.b2binpay.com/api/v1/virtualwallets/withdraws?filter%5Bvirtual_wallet_id%5D=45&filter%5Bcurrency%5D=1000&sort=-id&pagesize=50&page=1
 * [last] => https://gw-test.b2binpay.com/api/v1/virtualwallets/withdraws?filter%5Bvirtual_wallet_id%5D=45&filter%5Bcurrency%5D=1000&sort=-id&pagesize=50&page=1
 * [prev] =>
 * [next] =>
 * )
 * [meta] => stdClass Object
 * (
 * [current_page] => 1
 * [from] => 1
 * [last_page] => 1
 * [path] => https://gw-test.b2binpay.com/api/v1/virtualwallets/withdraws
 * [per_page] => 50
 * [to] => 46
 * [total] => 46
 * )
 * )
 *
 */
