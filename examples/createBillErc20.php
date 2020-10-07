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

$baseCurrency = (string)getenv('BASE_CURRENCY');


// Simulate wallets in your application, used to create an bill (invoice)
$wallets = [
    1 => [
        'id' => 141, // <- Change to your BTC wallet ID
        'currency' => 'btc'
    ],
    2 => [
        'id' => 142, // <- Change to your ETH wallet ID
        'currency' => 'eth'
    ],
    3 => [
        'id' => 407, // <- Change to your USDT-OMNI wallet ID
        'currency' => 'usdt-omni'
    ],
    4 => [
        'id' => 442, // <- Change to your USDT-ETH wallet ID
        'currency' => 'usdt-eth'
    ],
    // ...
];
// Select wallets
$walletEth = $wallets[2];
$walletErc20 = $wallets[4];



// Bill Request
$billRequest = [
    'amount' => '1000.00', // Amount in USD
    'lifetime' => 0, // In seconds
    'track_id' => (string)time(),
    'callback_url' => null
];

// Generate callback Url from $_SERVER
// $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http');
// $billRequest['callback_url'] = $protocol.'://'.$_SERVER['HTTP_HOST'].'/callback.php';

// Create B2Binpay Provider object
$provider = new B2Binpay\Provider(
        $authKey,
        $authSecret,
        true // sandbox
    );

// Get actual Rates
$rates = $provider->getRates($baseCurrency);

// Convert currency
$cleanAmount = $provider->convertCurrency(
    $billRequest['amount'],
    $baseCurrency,
    $walletErc20['currency'],
    $rates
);

// Add 10% markup
$amount = $provider->addMarkup($cleanAmount, $walletErc20['currency'], 10);

// For ERC20 tokens, you need to create an bill on ETH and then create an bill for the token
$billEth = $provider->createBill(
        $walletEth['id'],
        '1',
        $walletEth['currency'],
        $billRequest['lifetime'],
        $billRequest['track_id'],
        $billRequest['callback_url'],
        null,
        null
    );

// Get the address of the parent bill $billEth->address and substitute it into bill creation for the ERC20
$billErc20 = $provider->createBill(
        $walletErc20['id'],
        $amount,
        $walletErc20['currency'],
        $billRequest['lifetime'],
        $billRequest['track_id'],
        $billRequest['callback_url'],
        null,
        null,
        $billEth->address // The address of the parent bill must be specified here
    );

echo '<pre>';
print_r($billErc20);
echo '</pre>';

// stdClass Object
// (
//     [id] => 18148
//     [url] => https://cr-test.b2binpay.com/eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCMkJDcnlwdG9QYXkiLCJzdWIiOjE4MTQ4LCJpYXQiOjE2MDE4OTc2NTd9.C_T4WO-AkdnJZcIcFS-riDWVl6IfQYJcpcxIdEGDXIE
//     [address] => 5f7b04b9786608d8f733a7c2a2ea60df6439a28a2b9a3
//     [created] => 2020-10-05 11:34:17
//     [expired] =>
//     [status] => 1
//     [tracking_id] => 1601897656
//     [callback_url] =>
//     [success_url] =>
//     [error_url] =>
//     [amount] => 1098287256000000000000
//     [actual_amount] => 0
//     [pow] => 18
//     [transactions] => Array
//     (
//     )
//     [currency] => stdClass Object
//     (
//         [iso] => 2005
//         [alpha] => USDT
//     )
//    [message] =>
// )
