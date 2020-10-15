<?php
require_once __DIR__ . '/config.php';

// Select wallet and currency
$virtual_wallet_id = getVwId();
$virtual_wallet_currency = getVwCurrency();

if (empty($virtual_wallet_id) || empty($virtual_wallet_currency)) {
    echo '<pre>Please, set VW_ID and VW_CURRENCY variables in .env</pre>';
    exit();
}

// Withdrawal Request
$withdrawal_request = [
    'amount' => '1000.00', // Amount in base currency
    'currency' => 'BTC', // Withdrawal in BTC
    'address' => 'Test872sjhfbw4jsgfuaTf4jhasdfg',
    'unique_id' => time(), // Any unique positive number not previously used
    'track_id' => (string)time(),
    'callback_url' => null
];

// Generate callback Url from $_SERVER
// $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http');
// $withdrawal_request['callback_url'] = $protocol.'://'.$_SERVER['HTTP_HOST'].'/callback.php';

// Create B2Binpay Provider object
$provider = new B2Binpay\Provider(
    getAuthKey(),
    getAuthSecret(),
    true // sandbox
);

// Get actual rates
$rates = $provider->getRates($virtual_wallet_currency);

// Convert currency
$amount = $provider->convertCurrency(
    $withdrawal_request['amount'],
    $virtual_wallet_currency,
    $withdrawal_request['currency'],
    $rates
);

// Create Withdrawal
$withdrawal = $provider->createWithdrawal(
    $virtual_wallet_id,
    $amount,
    $withdrawal_request['currency'],
    $withdrawal_request['address'],
    $withdrawal_request['unique_id'],
    $withdrawal_request['track_id'],
    $withdrawal_request['callback_url']
);

echo '<pre>';
print_r($withdrawal);
echo '</pre>';

/**
 * @example
 * stdClass Object
 * (
 * [id] => 1072
 * [virtual_wallet_id] => 45
 * [with_fee] =>
 * [created] => 2020-10-05 10:21:30
 * [address] => Test872sjhfbw4jsgfuaTf4jhasdfg
 * [message] =>
 * [amount] => 9367000
 * [fee] => NOT SET
 * [pow] => 8
 * [status] => 0
 * [transaction] =>
 * [tracking_id] =>
 * [unique_id] => 1601893289
 * [callback_url] =>
 * [currency] => stdClass Object
 * (
 * [iso] => 1000
 * [alpha] => BTC
 * )
 * )
 *
 */
