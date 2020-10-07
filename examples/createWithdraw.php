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



// Simulate virtual wallets in your application, used to create an withdrawal
$virtualWallets = [
    1 => [
        'id' => 45,
        'currency' => 'usd'
    ],
    // ...
];
// Select wallet
$virtualWallet = $virtualWallets[1];



// Withdrawal Request
$withdrawalRequest = [
    'amount' => '1000.00', // Amount in USD
    'currency' => 'BTC', // Withdrawal in BTC
    'address' => 'Test872sjhfbw4jsgfuaTf4jhasdfg',
    'unique_id' => time(), // Any unique positive number not previously used
    'track_id' => (string)time(),
    'callback_url' => null
];

// Generate callback Url from $_SERVER
// $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http');
// $withdrawalRequest['callback_url'] = $protocol.'://'.$_SERVER['HTTP_HOST'].'/callback.php';

// Create B2Binpay Provider object
$provider = new B2Binpay\Provider(
        $authKey,
        $authSecret,
        true // sandbox
    );

// Get actual Rates
$rates = $provider->getRates($baseCurrency);

// Convert currency
$amount = $provider->convertCurrency(
    $withdrawalRequest['amount'],
    $baseCurrency,
    $withdrawalRequest['currency'],
    $rates
);

// Create Withdrawal
$withdrawal = $provider->createWithdrawal(
        $virtualWallet['id'],
        $amount,
        $withdrawalRequest['currency'],
        $withdrawalRequest['address'],
        $withdrawalRequest['unique_id'],
        $withdrawalRequest['track_id'],
        $withdrawalRequest['callback_url']
    );

 echo '<pre>';
 print_r($withdrawal);
 echo '</pre>';

// stdClass Object
// (
//     [id] => 1072
//     [virtual_wallet_id] => 45
//     [with_fee] =>
//     [created] => 2020-10-05 10:21:30
//     [address] => Test872sjhfbw4jsgfuaTf4jhasdfg
//     [message] =>
//     [amount] => 9367000
//     [fee] => NOT SET
//     [pow] => 8
//     [status] => 0
//     [transaction] =>
//     [tracking_id] =>
//     [unique_id] => 1601893289
//     [callback_url] =>
//     [currency] => stdClass Object
//     (
//         [iso] => 1000
//         [alpha] => BTC
//     )
// )
