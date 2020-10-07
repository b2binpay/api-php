<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

// Load Composer
require_once dirname(__DIR__).'/vendor/autoload.php';

// Load params from Dotenv
$dotenv = new Dotenv\Dotenv(dirname(__DIR__));
$dotenv->load();

$authKey = (string)getenv('AUTH_KEY');
$authSecret = (string)getenv('AUTH_SECRET');

// Create B2Binpay Currency object
$currency = new B2Binpay\Currency();

$virtualWalletsParam = [
        'query' => [
            'filter' => [
                // 'virtual_wallet_id' => 45, // (int) Get info on virtual wallet id 45
                // 'currency' => $currency->getIso('BTC'), // (int) ISO code currency
                // 'amount' => 100, // (float) Virtual wallet balance
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
//                 'amount' => 'gte', // Virtual wallet will be filter whose amount is more than or equal to $virtualWalletsParam['query]['filter']['amount'];
            ],
            'sort' => '-id', // Reverse sorting by ID field, defaul: id
            'pagesize' => 50, // Maximum results per page 250, default: 20
            'page' => 1
        ]
    ];

// Create B2Binpay Provider object
$provider = new B2Binpay\Provider(
        $authKey,
        $authSecret,
        true // sandbox
    );

// Get virtual wallets
$virtualWallets = $provider->getVirtualWallets($virtualWalletsParam);
// Or get virtual wallets by id
// $virtualWalletId = 45;
// $virtualWallet = $provider->getVirtualWallet($virtualWalletId);

echo '<pre>';
print_r($virtualWallets);
echo '</pre>';

// stdClass Object
// (
//     [data] => Array
//     (
//         [0] => stdClass Object
//         (
//             [id] => 83
//             [active] => 1
//             [name] => withdrawals_usdt
//             [amount] => 9321718870
//             [pow] => 8
//             [currency] => stdClass Object
//             (
//                  [iso] => 2005
//                  [alpha] => USDT
//             )
//             [next_unique_id] => 20200911112835
//         )
//         [1] => stdClass Object
//         (
//             [id] => 46
//             [active] => 1
//             [name] => withdrawals_eth
//             [amount] => 122076514489500000000
//             [pow] => 18
//             [currency] => stdClass Object
//             (
//                 [iso] => 1002
//                 [alpha] => ETH
//             )
//             [next_unique_id] => 20200911112835
//         )
//         [2] => stdClass Object
//         (
//             [id] => 45
//             [active] => 1
//             [name] => withdrawals_btc
//             [amount] => 9881765048
//             [pow] => 8
//             [currency] => stdClass Object
//             (
//                 [iso] => 1000
//                 [alpha] => BTC
//             )
//             [next_unique_id] => 20200911112835
//         )
//     )
//     [links] => stdClass Object
//     (
//         [first] => https://gw-test.b2binpay.com/api/v1/virtualwallets/wallets?sort=-id&pagesize=50&page=1
//         [last] => https://gw-test.b2binpay.com/api/v1/virtualwallets/wallets?sort=-id&pagesize=50&page=1
//         [prev] =>
//         [next] =>
//     )
//     [meta] => stdClass Object
//     (
//         [current_page] => 1
//         [from] => 1
//         [last_page] => 1
//         [path] => https://gw-test.b2binpay.com/api/v1/virtualwallets/wallets
//         [per_page] => 50
//         [to] => 5
//         [total] => 5
//     )
// )
