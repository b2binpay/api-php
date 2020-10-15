<?php
require_once __DIR__ . '/config.php';

// Create B2Binpay Currency object
$currency = new B2Binpay\Currency();

$transactions_param = [
    'query' => [
        'filter' => [
            // 'bill_id' => 14033, // (int) Get all transactions on the bill 14033
            // 'address' => '5f7af8aed5fa7a9df2255ad642b923d95503b9a7958d8' // (string) Blockchain address or destination tag (memo, message) bill
            'currency' => $currency->getIso('BTC'), // (int) ISO code currency
            // 'status' => 2, // (int) See in list of transactions statuses
            // 'amount' => 9.1, // (float) Amount from which the deposit was received
            // 'transaction' => 'NWY1YjQ0NDExZGI4YmUwMmUyN2UwNGZkZmY5NjdiYTdkNzZmYjI0YjgwNjlkIzAuMDEwNjY1NjAwMDAwMDAwMDAw', // (string) Blockchain hash transactions
            // 'tracking_id' => '202010010001', // (string) Track for bill tracking
            // 'created' => '2020-10-01 00:00:00,2020-10-01 23:59:59' // (string) Transaction creation date or between dates
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
            // 'amount' => 'gte', // Transactions will be filter whose amount is more than or equal to $transactionsParam['query]['filter']['amount'];
            // 'created' => 'bt', // Date of creation between $transactionsParam['query]['filter']['created']
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

// Get transactions
$transactions = $provider->getTransactions($transactions_param);
// Or get transaction by id
// $transactionId = 14033;
// $transaction = $provider->getTransaction($transactionId);

echo '<pre>';
print_r($transactions);
echo '</pre>';

/**
 * @example
 * stdClass Object
 * (
 * [data] => Array
 * (
 * [0] => stdClass Object
 * (
 * [id] => 15499
 * [bill_id] => 17275
 * [created] => 2020-09-11 09:35:01
 * [amount] => 10665600000000000
 * [pow] => 18
 * [status] => 2
 * [transaction] => NWY1YjQ0NDExZGI4YmUwMmUyN2UwNGZkZmY5NjdiYTdkNzZmYjI0YjgwNjlkIzAuMDEwNjY1NjAwMDAwMDAwMDAw
 * [type] => 0
 * [currency] => stdClass Object
 * (
 * [iso] => 1000
 * [alpha] => BTC
 * )
 * )
 * [1] => stdClass Object
 * (
 * [id] => 15497
 * [bill_id] => 17272
 * [created] => 2020-09-11 09:25:01
 * [amount] => 10641400000000000
 * [pow] => 18
 * [status] => 2
 * [transaction] => NWY1YjQxZWExYWMyMjFhYmIxZTFlYTVmNDgxYjU4OWRhNTIzMDNiMDkxY2JiIzAuMDEwNjQxNDAwMDAwMDAwMDAw
 * [type] => 0
 * [currency] => stdClass Object
 * (
 * [iso] => 1000
 * [alpha] => BTC
 * )
 * )
 * [2] => stdClass Object
 * (
 * [id] => 15496
 * [bill_id] => 17271
 * [created] => 2020-09-11 09:00:02
 * [amount] => 10637000000000000
 * [pow] => 18
 * [status] => 2
 * [transaction] => NWY1YjNjMzY4YTI1ZjM3MWJjZTdkYzgzODE3Yjc4OTNiY2RlZWQxMzc5OWI1IzAuMDEwNjM3MDAwMDAwMDAwMDAw
 * [type] => 0
 * [currency] => stdClass Object
 * (
 * [iso] => 1000
 * [alpha] => BTC
 * )
 * )
 * ...
 * )
 * [links] => stdClass Object
 * (
 * [first] => https://gw-test.b2binpay.com/api/v1/pay/transactions?filter%5Bcurrency%5D=1000&sort=-id&pagesize=50&page=1
 * [last] => https://gw-test.b2binpay.com/api/v1/pay/transactions?filter%5Bcurrency%5D=1000&sort=-id&pagesize=50&page=2
 * [prev] =>
 * [next] => https://gw-test.b2binpay.com/api/v1/pay/transactions?filter%5Bcurrency%5D=1000&sort=-id&pagesize=50&page=2
 * )
 * [meta] => stdClass Object
 * (
 * [current_page] => 1
 * [from] => 1
 * [last_page] => 2
 * [path] => https://gw-test.b2binpay.com/api/v1/pay/transactions
 * [per_page] => 50
 * [to] => 50
 * [total] => 70
 * )
 * )
 *
 */
