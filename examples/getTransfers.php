<?php
require_once __DIR__ . '/config.php';

// Create B2Binpay Currency object
$currency = new B2Binpay\Currency();

$transfers_param = [
    'query' => [
        'filter' => [
            // 'external_id' => 14033, // (int) ID transaction or id withdrawal in B2BinPay system
            // 'virtual_wallet_id' => 45, // (int) ID virtual wallet
            'type' => 0, // (int) See in list of transfers types
            'source_currency' => $currency->getIso('BTC'), // (int) Source ISO code currency
            // 'target_currency' => $currency->getIso('USDT'), // (int) Target ISO code currency
            // 'status' => 1, // (int) See in list of transfers statuses
            // 'created' => '2020-10-01 00:00:00,2020-10-01 23:59:59' // (string) Transfer creation date or between dates
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
            // 'created' => 'bt', // Date of creation between $transfersParam['query]['filter']['created']
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

// Get transfers
$transfers = $provider->getTransfers($transfers_param);
// Or get transfer by id
// $transfer_id = 6755;
// $transfer = $provider->getTransfer($transfer_id);

echo '<pre>';
print_r($transfers);
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
 * [id] => 4551
 * [virtual_wallet_id] => 45
 * [external_id] => 6755
 * [created] => 2020-10-01 13:01:37
 * [source_amount] => 1256983
 * [source_pow] => 8
 * [target_amount] => 1256983
 * [target_pow] => 8
 * [rate] => 100000000
 * [rate_pow] => 8
 * [status] => 1
 * [transaction] => NWQ0YzFhYTVjYzFmYzFmMzZjMTVkNmEzZDE4ZDUyZThkNDkzYmM4MTg3Y2I5IzAuMDEyNjMzMDAwMDAwMDAwMDAw
 * [tracking_id] =>
 * [type] => 0
 * [source_currency] => stdClass Object
 * (
 * [iso] => 1000
 * [alpha] => BTC
 * )
 * [target_currency] => stdClass Object
 * (
 * [iso] => 1000
 * [alpha] => BTC
 * )
 * )
 * [1] => stdClass Object
 * (
 * [id] => 4547
 * [virtual_wallet_id] => 45
 * [external_id] => 6752
 * [created] => 2020-10-01 12:35:02
 * [source_amount] => 837690
 * [source_pow] => 8
 * [target_amount] => 837690
 * [target_pow] => 8
 * [rate] => 100000000
 * [rate_pow] => 8
 * [status] => 1
 * [transaction] => NWQ0YzE2MDUwYWFhZmQ3OWM2MjU2YjliZGFjNTNhNTU4MDFhMDY2YjcwZGEzIzAuMDA4NDE5MDAwMDAwMDAwMDAw
 * [tracking_id] =>
 * [type] => 0
 * [source_currency] => stdClass Object
 * (
 * [iso] => 1000
 * [alpha] => BTC
 * )
 * [target_currency] => stdClass Object
 * (
 * [iso] => 1000
 * [alpha] => BTC
 * )
 * )
 * ...
 * )
 * [links] => stdClass Object
 * (
 * [first] => https://gw-test.b2binpay.com/api/v1/virtualwallets/transfers?filter%5Btype%5D=0&filter%5Bsource_currency%5D=1000&sort=-id&pagesize=50&page=1
 * [last] => https://gw-test.b2binpay.com/api/v1/virtualwallets/transfers?filter%5Btype%5D=0&filter%5Bsource_currency%5D=1000&sort=-id&pagesize=50&page=2
 * [prev] =>
 * [next] => https://gw-test.b2binpay.com/api/v1/virtualwallets/transfers?filter%5Btype%5D=0&filter%5Bsource_currency%5D=1000&sort=-id&pagesize=50&page=2
 * )
 * [meta] => stdClass Object
 * (
 * [current_page] => 1
 * [from] => 1
 * [last_page] => 2
 * [path] => https://gw-test.b2binpay.com/api/v1/virtualwallets/transfers
 * [per_page] => 50
 * [to] => 50
 * [total] => 69
 * )
 * )
 *
 */
