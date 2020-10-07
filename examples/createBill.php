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
// Select wallet
$wallet = $wallets[4];



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
    $wallet['currency'],
    $rates
);

// Add 10% markup
$amount = $provider->addMarkup($cleanAmount, $wallet['currency'], 10);

// Create Bill
$bill = $provider->createBill(
        $wallet['id'],
        $amount,
        $wallet['currency'],
        $billRequest['lifetime'],
        $billRequest['track_id'],
        $billRequest['callback_url'],
        null,
        null
    );

echo '<pre>';
print_r($bill);
echo '</pre>';

// stdClass Object
// (
//     [id] => 18142
//     [url] => https://cr-test.b2binpay.com/eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCMkJDcnlwdG9QYXkiLCJzdWIiOjE4MTQyLCJpYXQiOjE2MDE4OTQ1NzR9.9ylQdkQwn4hX2USTCOo8IbSY8qAzpiHQQfsISumCFq0
//     [address] => 5f7af8aed5fa7a9df2255ad642b923d95503b9a7958d8
//     [created] => 2020-10-05 10:42:54
//     [expired] =>
//     [status] => 1
//     [tracking_id] => 1601894573
//     [callback_url] =>
//     [success_url] =>
//     [error_url] =>
//     [amount] => 1098182228000000000000
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
//     [message] =>
// )

