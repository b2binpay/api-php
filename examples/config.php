<?php
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');


// Load Composer
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Load params from Dotenv
$dotenv = new Dotenv\Dotenv(dirname(__DIR__));
$dotenv->load();

function getAuthKey(): string
{
    return (string)getenv('AUTH_KEY');
}

function getAuthSecret(): string
{
    return (string)getenv('AUTH_SECRET');
}

function getBaseCurrency(): string
{
    return (string)(getenv('BASE_CURRENCY') ?? 'USD');
}

/**
 * Simulates wallets in your application, used to create an bill (invoice)
 * Uses only BTC, ETH, USDT-ETH currencies
 * @return array
 */
function getWallets(): array
{
    return [
        'BTC' => (int)getenv('BTC'),
        'ETH' => (int)getenv('ETH'),
        'USDT-ETH' => (int)getenv('USDT-ETH'),
    ];
}

function getVwId(): ?int {
    return (int)getenv('VW_ID');
}

function getVwCurrency(): ?string {
    return (string)getenv('VW_CURRENCY');
}
