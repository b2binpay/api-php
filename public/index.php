<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

// Load Composer
require_once dirname(__DIR__).'/vendor/autoload.php';

// Load params from Dotenv
$dotenv = new Dotenv\Dotenv(dirname(__DIR__));
$dotenv->load();

$authKey        = getenv('AUTH_KEY');
$authSecret     = getenv('AUTH_SECRET');
$walletID       = (int)getenv('WALLET');
$baseCurrency   = getenv('CURRENCY');
$lifetime       = (int)getenv('LIFETIME');

// Create B2Binpay Provider object
$provider = new B2Binpay\Provider($authKey, $authSecret, true);

/*
 * Check $_POST for callback and save it to 'callback.txt'
 */
if (!empty($_POST)) {
    // Get POST headers
    $headers = getallheaders();

    // Check callback Authorization
    if (empty($headers['Authorization']) || ($headers['Authorization'] !== $provider->getAuthorization())) {
        header('HTTP/1.1 401 Unauthorized');
        exit();
    }

    $json_string = json_encode($_POST);

    // Write it to 'callback.txt'
    file_put_contents(
        'callback.txt',
        date('Y/M/d H:i:s').PHP_EOL.$json_string.PHP_EOL.PHP_EOL,
        FILE_APPEND | LOCK_EX
    );

    // Send proper response to callback
    header('HTTP/1.1 200 OK');
    exit('OK');
}

/*
 * If there no callback, let's create test payment
 */

// Get Wallet info
$wallet = $provider->getWallet($walletID);

// Get actual Rates
$rates = $provider->getRates($baseCurrency);

// Convert currency
$cleanAmount = $provider->convertCurrency(
    '1',
    $baseCurrency,
    $wallet->currency->alpha,
    $rates
);

// Add 10% markup
$amount = $provider->addMarkup($cleanAmount, $wallet->currency->alpha, 10);

// Generate callback Url from $_SERVER
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http');
$callbackUrl = $protocol.'://'.$_SERVER['HTTP_HOST'];

// Generate tracking ID from timestamp
$trackingID = (string)time();

// Create Bill
$bill = $provider->createBill(
    $wallet->id,
    $amount,
    $wallet->currency->alpha,
    $lifetime,
    $trackingID,
    $callbackUrl
);

// Check Bill
$billUpd = $provider->getBill($bill->id);

echo '<pre>';
print_r($wallet);
print_r($billUpd);
echo '</pre>';
