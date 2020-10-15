<?php
require_once __DIR__ . '/config.php';

// Create B2Binpay Currency object
$currency = new B2Binpay\Currency();

$bills_param = [
    'query' => [
        'filter' => [
            'wallet_id' => getWallets()['BTC'], // (int) Select bills only created for wallet 442
            // 'address' => '5f7af8aed5fa7a9df2255ad642b923d95503b9a7958d8' // (string) Blockchain address or destination tag (memo, message) bill
            // 'currency' => $currency->getIso('BTC'), // (int) ISO currency code
            'status' => 2, // (int) See in list of bills statuses
            // 'amount' => 9.1, // (float) The amount requested to create the bill
            // 'tracking_id' => '202010010001', // (string) Track for bill tracking
            'created' => '2020-05-01 00:00:00,2020-10-01 23:59:59' // (string) Bill creation date or between dates
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
            'amount' => 'gte', // Bills will be filter whose amount is more than or equal to $billsParam['query]['filter']['amount'];
            'created' => 'bt', // Date of creation between $billsParam['query]['filter']['created']
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

// Get bills
$bills = $provider->getBills($bills_param);
// Or get bill by id
// $bill_id = 14033;
// $bill = $provider->getBill($bill_id);

echo '<pre>';
print_r($bills);
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
 * [id] => 14765
 * [url] => https://gw-test.b2binpay.com/eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCMkJDcnlwdG9QYXkiLCJzdWIiOjE0NzY1LCJpYXQiOjE1OTA1Mzk0MDQsImV4cCI6MTU5MDYyNTQ4MH0.Rs1bgsvtqHqqJ-QvmpygNMeitqPHZZAPM0lhqSMH_Kg
 * [address] => 5ecdb3487f05f9f96f36b7aae3b1ff847c26ac94c604e
 * [created] => 2020-10-01 00:30:04
 * [expired] => 2020-10-02 00:24:40
 * [status] => 2
 * [tracking_id] => 2605200805494941
 * [callback_url] =>
 * [success_url] =>
 * [error_url] =>
 * [amount] => 14000000000000000000
 * [actual_amount] => 74000000000000000000
 * [pow] => 18
 * [transactions] => Array
 * (
 * [0] => stdClass Object
 * (
 * [id] => 12913
 * [bill_id] => 14765
 * [created] => 2020-10-01 00:30:04
 * [amount] => 14000000000000000000
 * [pow] => 18
 * [status] => 1
 * [transaction] => NWVjZGIzNDg3ZjA1ZjlmOTZmMzZiN2FhZTNiMWZmODQ3YzI2YWM5NGM2MDRlIzE0LjAwMDAwMDAwMDAwMDAwMDAwMA
 * [type] => 0
 * [currency] => stdClass Object
 * (
 * [iso] => 2005
 * [alpha] => USDT
 * )
 *
 * )
 * [1] => stdClass Object
 * (
 * [id] => 12914
 * [bill_id] => 14765
 * [created] => 2020-10-01 00:30:04
 * [amount] => 20000000000000000000
 * [pow] => 18
 * [status] => 1
 * [transaction] => NWVjZGIzNDg3ZjA1ZjlmOTZmMzZiN2FhZTNiMWZmODQ3YzI2YWM5NGM2MDRlIzIwLjAwMDAwMDAwMDAwMDAwMDAwMA
 * [type] => 0
 * [currency] => stdClass Object
 * (
 * [iso] => 2005
 * [alpha] => USDT
 * )
 * )
 * )
 * [message] =>
 * )
 * [1] => stdClass Object
 * (
 * [id] => 14768
 * [url] => https://gw-test.b2binpay.com/eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCMkJDcnlwdG9QYXkiLCJzdWIiOjE0NzY4LCJpYXQiOjE1OTA1NDAzMDMsImV4cCI6MTU5MDYyNjM1Mn0.WN4e4IBgeOYYHAoB-M7IIqbIC3aV66ksQsycSftVGzQ
 * [address] => 5ecdb6b0e5a92f9d1152547c0bde01830b7e8bd60024c
 * [created] => 2020-10-01 00:45:03
 * [expired] => 2020-10-02 00:39:12
 * [status] => 2
 * [tracking_id] => 2605200805363233
 * [callback_url] => https://ynaps-test.tk/order-callback
 * [success_url] => https://ynaps-test.tk/order-processed
 * [error_url] => https://ynaps-test.tk/order-processed
 * [amount] => 15000000000000000000
 * [actual_amount] => 75000000000000000000
 * [pow] => 18
 * [transactions] => Array
 * (
 * [0] => stdClass Object
 * (
 * [id] => 12916
 * [bill_id] => 14768
 * [created] => 2020-10-01 00:45:03
 * [amount] => 15000000000000000000
 * [pow] => 18
 * [status] => 1
 * [transaction] => NWVjZGI2YjBlNWE5MmY5ZDExNTI1NDdjMGJkZTAxODMwYjdlOGJkNjAwMjRjIzE1LjAwMDAwMDAwMDAwMDAwMDAwMA
 * [type] => 0
 * [currency] => stdClass Object
 * (
 * [iso] => 2005
 * [alpha] => USDT
 * )
 * )
 * [1] => stdClass Object
 * (
 * [id] => 12917
 * [bill_id] => 14768
 * [created] => 2020-10-01 00:45:04
 * [amount] => 20000000000000000000
 * [pow] => 18
 * [status] => 1
 * [transaction] => NWVjZGI2YjBlNWE5MmY5ZDExNTI1NDdjMGJkZTAxODMwYjdlOGJkNjAwMjRjIzIwLjAwMDAwMDAwMDAwMDAwMDAwMA
 * [type] => 0
 * [currency] => stdClass Object
 * (
 * [iso] => 2005
 * [alpha] => USDT
 * )
 * )
 * )
 * [message] =>
 * )
 * ...
 * )
 * [links] => stdClass Object
 * (
 * [first] => https://gw-test.b2binpay.com/api/v1/pay/bills?filter%5Bwallet_id%5D=442&pagesize=50&page=1
 * [last] => https://gw-test.b2binpay.com/api/v1/pay/bills?filter%5Bwallet_id%5D=442&pagesize=50&page=1
 * [prev] =>
 * [next] =>
 * )
 * [meta] => stdClass Object
 * (
 * [current_page] => 1
 * [from] => 1
 * [last_page] => 1
 * [path] => https://gw-test.b2binpay.com/api/v1/pay/bills
 * [per_page] => 50
 * [to] => 3
 * [total] => 3
 * )
 * )
 *
 */
