<?php
require_once __DIR__ . '/config.php';

// Select wallet and set currency
$currency = 'BTC';
$wallet_id = getWallets()[$currency];

if (empty($wallet_id)) {
    echo '<pre>Please, set wallet id for BTC variable in .env</pre>';
    exit();
}

// Bill Request
$bill_request = [
    'amount' => '1000.00', // Amount in base currency
    'lifetime' => 0, // In seconds
    'track_id' => (string)time(),
    'callback_url' => null
];

// Generate callback Url from $_SERVER
// $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http');
// $bill_request['callback_url'] = $protocol.'://'.$_SERVER['HTTP_HOST'].'/callback.php';

// Create B2Binpay Provider object
$provider = new B2Binpay\Provider(
    getAuthKey(),
    getAuthSecret(),
    true // sandbox
);

// Get actual Rates
$rates = $provider->getRates(getBaseCurrency());

// Convert currency
$clean_amount = $provider->convertCurrency(
    $bill_request['amount'],
    getBaseCurrency(),
    $currency,
    $rates
);

// Add 10% markup
$amount = $provider->addMarkup($clean_amount, $currency, 10);

// Create Bill
$bill = $provider->createBill(
    $wallet_id,
    $amount,
    $currency,
    $bill_request['lifetime'],
    $bill_request['track_id'],
    $bill_request['callback_url'],
    null,
    null
);

echo '<pre>';
print_r($bill);
echo '</pre>';

/**
 * @example
 * stdClass Object
 * (
 * [id] => 18142
 * [url] => https://gw-test.b2binpay.com/eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCMkJDcnlwdG9QYXkiLCJzdWIiOjE4MTQyLCJpYXQiOjE2MDE4OTQ1NzR9.9ylQdkQwn4hX2USTCOo8IbSY8qAzpiHQQfsISumCFq0
 * [address] => 5f7af8aed5fa7a9df2255ad642b923d95503b9a7958d8
 * [created] => 2020-10-05 10:42:54
 * [expired] =>
 * [status] => 1
 * [tracking_id] => 1601894573
 * [callback_url] =>
 * [success_url] =>
 * [error_url] =>
 * [amount] => 1098182228000000000000
 * [actual_amount] => 0
 * [pow] => 18
 * [transactions] => Array
 * (
 * )
 * [currency] => stdClass Object
 * (
 * [iso] => 2005
 * [alpha] => USDT
 * )
 * [message] =>
 * )
 */