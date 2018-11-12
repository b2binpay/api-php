# B2BinPAY API client for PHP

Accepting [Bitcoin](https://bitcoin.org/), [Bitcoin Cash](https://www.bitcoincash.org/), [Ethereum](https://www.ethereum.org/), [DASH](https://www.dash.org/), [Litecoin](https://litecoin.org/), [Monero](https://getmonero.org/), [NEO](https://neo.org), [NEM](https://nem.io/), [Ripple](https://ripple.com/), [B2BX](https://b2bx.org/) and any ERC20, NEO tokens in one place!

## Requirements

+ PHP >= 7.0
+ PHP extensions enabled: cURL, JSON

## Composer Installation

The easiest way to install the B2BinPAY API client is to require it with [Composer](http://getcomposer.org/doc/00-intro.md) through command-line:
```
    $ composer require b2binpay/api-php
```
or by editing `composer.json`:
```
    {
        "require": {
            "b2binpay/api-php": "^1.0"
        }
    }
```

## Getting started

Use the API key and secret to access your B2BinPAY account:

```php
$provider = new B2Binpay\Provider(
    'API_KEY',
    'API_SECRET'
);
``` 

### Test Mode

In order to use testing sandbox, pass `true` as a third parameter for `B2Binpay\Provider`:

```php
$provider = new B2Binpay\Provider(
    'API_KEY',
    'API_SECRET',
    true
);
``` 

**Warning: Sandbox and main gateway has their own pairs of key and secret!**

### Create bill

_The payment currency is considered to match the currency of your wallet_.

Create a new bill:

```php
$bill = $provider->createBill(
    WALLET_ID,
    '0.00000001',
    'BTC',
    LIFETIME
);
```
_where `LIFETIME` - number of seconds for payment page to live and `WALLET_ID` - your B2BinPAY wallet id._

Now the bill id is available in the `$bill->id` property. You should store this id with your order.

After storing the bill id you can find an url to the payment page in the `$bill->url` property.  

Finally, you can check bill status by requesting it using the stored id:

```php
$billCheck = $provider->getBill($bill->id);
```

Status will be stored in `$billCheck->status` property.

### Convert currency

You can get actual rates and convert supported currencies respecting your wallet's parameters.

Get rates for _USD_:

```php
$rates = $provider->getRates('USD');
```

Convert currency using actual rates:

```php
$amount = $provider->convertCurrency('100', 'USD', 'BTC', $rates);
```

Now you can provide `$amount` variable as a second parameter for `createBill()` method to set accurate amount of cryptocurrency.

### Add markup

You can add some markup for existing amount.

Set _10%_ markup for current amount:

```php
$amount = $provider->addMarkup($amount, 'BTC', 10);
```

### Get your wallet's params

You can retrieve your wallet's params to find out actual currency and current amount:

```php
$wallet = $provider->getWallet(WALLET_ID);
```

Now your wallet's currency alpha code is stored in the `$wallet->currency->alpha` parameter.

You can use it for `createBill()`, `addMarkup()` and `convertCurrency()` methods.

### List of bill statuses

| Status | Description |
| --- | --- |
| -2 | Payment error |
| -1 | Payment lifetime is exceeded |
| 1 | Payment pending |
| 2 | Payment success |
| 3 | Payment freeze |
| 4 | Payment closed (funds withdrawn) |

## License
   
B2BinPAY\API-PHP is licensed under the [GPL License](https://github.com/b2binpay/api-php/blob/master/LICENSE).
