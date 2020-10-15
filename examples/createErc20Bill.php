<?php
require_once __DIR__ . '/config.php';

// Select erc20 wallet
$eth_currency = 'ETH';
$erc20_currency = 'USDT-ETH';
$wallet_eth_id = getWallets()[$eth_currency];
$wallet_erc20_id = getWallets()[$erc20_currency];

if (empty($wallet_eth_id) || empty($wallet_erc20_id)) {
    echo '<pre>Please, set wallet id for ETH and USDT-ETH variables in .env</pre>';
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
    $erc20_currency,
    $rates
);

// Add 10% markup
$amount = $provider->addMarkup($clean_amount, $erc20_currency, 10);

// For ERC20 tokens, you need to create an bill on ETH and then create an bill for the token
$bill_eth = $provider->createBill(
    $wallet_eth_id,
    '1',
    $eth_currency,
    $bill_request['lifetime'],
    $bill_request['track_id'],
    $bill_request['callback_url'],
    null,
    null
);

// Get the address of the parent bill $bill_eth->address and substitute it into bill creation for the ERC20
$bill_erc20 = $provider->createBill(
    $wallet_erc20_id,
    $amount,
    $erc20_currency,
    $bill_request['lifetime'],
    $bill_request['track_id'],
    $bill_request['callback_url'],
    null,
    null,
    $bill_eth->address // The address of the parent bill must be specified here
);

echo '<pre>';
print_r($bill_erc20);
echo '</pre>';

/**
 * @example
 *
 * stdClass Object
 * (
 * [id] => 18148
 * [url] => https://gw-test.b2binpay.com/eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCMkJDcnlwdG9QYXkiLCJzdWIiOjE4MTQ4LCJpYXQiOjE2MDE4OTc2NTd9.C_T4WO-AkdnJZcIcFS-riDWVl6IfQYJcpcxIdEGDXIE
 * [address] => 5f7b04b9786608d8f733a7c2a2ea60df6439a28a2b9a3
 * [created] => 2020-10-05 11:34:17
 * [expired] =>
 * [status] => 1
 * [tracking_id] => 1601897656
 * [callback_url] =>
 * [success_url] =>
 * [error_url] =>
 * [amount] => 1098287256000000000000
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