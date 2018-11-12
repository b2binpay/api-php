<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once dirname(__DIR__) . '/vendor/autoload.php';

function printPreformatted($object)
{
    echo '<pre>';
    print_r($object);
    echo '</pre>';
}

$dotenv = new Dotenv\Dotenv((dirname(__DIR__)));
$dotenv->load();

/**
 * create Payment object
 */
$provider = new B2Binpay\Provider(
    (string)getenv('AUTH_KEY'),
    (string)getenv('AUTH_SECRET'),
    true
);

/**
 * get Wallet info
 */
$wallet = $provider->getWallet((int)getenv('WALLET'));

/**
 * get Rates
 */
$rates = $provider->getRates(getenv('CURRENCY'));

/**
 * convert currency
 */
$cleanAmount = $provider->convertCurrency(
    '1',
    getenv('CURRENCY'),
    $wallet->currency->alpha,
    $rates
);

/**
 * add 10% markup
 */
$amount = $provider->addMarkup($cleanAmount, $wallet->currency->alpha, 10);

/**
 * create Bill
 */
$bill = $provider->createBill(
    $wallet->id,
    $amount,
    $wallet->currency->alpha,
    (int)getenv('LIFETIME')
);

/**
 * check Bill
 */
$billUpd = $provider->getBill($bill->id);

printPreformatted($wallet);
printPreformatted($billUpd);
