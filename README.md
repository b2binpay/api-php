# B2BinPay API client for PHP

Accepting [Bitcoin](https://bitcoin.org/), [Bitcoin Cash](https://www.bitcoincash.org/)
, [Ethereum](https://www.ethereum.org/), [DASH](https://www.dash.org/), [Litecoin](https://litecoin.org/)
, [Monero](https://getmonero.org/), [NEO](https://neo.org), [NEM](https://nem.io/), [Ripple](https://ripple.com/)
, [Cardano](https://www.cardano.org/), [Dogecoin](https://dogecoin.com/), [Zcash](https://z.cash/)
, [Stellar](https://www.stellar.org/), [EOS](https://eos.io/), [TRON](https://tron.network/)
, [Binance Coin](https://www.binance.com/) and any ERC20 and stablecoin, NEO tokens in one place!

[![Build Status](https://travis-ci.org/b2binpay/api-php.svg?branch=master)](https://travis-ci.org/b2binpay/api-php) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/b2binpay/api-php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/b2binpay/api-php/?branch=master) [![Coverage Status](https://coveralls.io/repos/github/b2binpay/api-php/badge.svg?branch=master)](https://coveralls.io/github/b2binpay/api-php?branch=master)

## Requirements

+ [B2BinPay](https://b2binpay.com) account
+ PHP >= 7.1 (If you need 7.0, please, use [version 1.1.0](https://github.com/b2binpay/api-php/tree/1.1.0))
+ PHP extensions enabled: cURL, JSON

## Composer Installation

The easiest way to install the B2BinPay API client is to require it
with [Composer](http://getcomposer.org/doc/00-intro.md) through command-line:

```shell
composer require b2binpay/api-php
```

or by editing `composer.json`:

```json
    {
  "require": {
    "b2binpay/api-php": "^1.2"
  }
}
```

## Local installation

```bash
composer install --no-dev
cp .env.example .env
```

## Support currencies

| Currency | Name | Blockchain, links |
| --- | --- | --- |
| ADA | Cardano | [Cardano](https://www.cardano.org/) |
| BCH | Bitcoin Cash | [Bitcoin Cash](https://www.bitcoincash.org/) |
| BNB | Binance Coin | Binance Chain, [BEP2](https://explorer.binance.org/asset/BNB) |
| BTC | Bitcoin | [Bitcoin](https://bitcoin.org/) |
| BUSD-ETH | Binance USD | Ethereum, [Stablecoin](https://etherscan.io/token/0x4Fabb145d64652a948d72533023f6E7A623C7C53) |
| DAI-ETH | Dai | Ethereum, [Stablecoin](https://etherscan.io/token/0x6b175474e89094c44da98b954eedeac495271d0f) |
| DASH | Dash | [Dash](https://www.dash.org) |
| DOGE | Dogecoin | [Dogecoin](https://dogecoin.com) |
| EOS | EOS | [EOS](https://eos.io) |
| ETH | Ethereum | [Ethereum](https://ethereum.org/en/) |
| GUSD-ETH | Gemini Dollar | Ethereum, [Stablecoin](https://etherscan.io/token/0x056Fd409E1d7A124BD7017459dFEa2F387b6d5Cd) |
| LTC | Litecoin | [Litecoin](https://litecoin.org) |
| NEO | Neo | [Neo](https://neo.org) |
| PAX-ETH | Paxos Standard | Ethereum, [Stablecoin](https://etherscan.io/address/0x8e870d67f660d95d5be530380d0ec0bd388289e1) |
| TRX | TRON | [TRON](https://tron.network) |
| TUSD-ETH | TrueUSD | Ethereum, [Stablecoin](https://etherscan.io/token/0x0000000000085d4780B73119b644AE5ecd22b376) |
| USDC-ETH | USD Coin | Ethereum, [Stablecoin](https://etherscan.io/token/0xa0b86991c6218b36c1d19d4a2e9eb0ce3606eb48) |
| USDT-ETH | Tether | Ethereum, [Stablecoin](https://etherscan.io/address/0xdac17f958d2ee523a2206206994597c13d831ec7) |
| USDT-OMNI | Tether | OMNI, [Stablecoin](https://www.omniexplorer.info/asset/31) |
| XEM | NEM | [NEM](https://www.nem.io) |
| XLM | Stellar | [Stellar](https://www.stellar.org) |
| XMR | Monero | [Monero](https://www.getmonero.org) |
| XRP | Ripple | [Ripple](https://ripple.com/xrp/) |
| ZEC | Zcash | [Zcash](https://z.cash) |

## Getting started

See examples with comments in [examples/README.md](examples/README.md)

### Create Provider instance

Use the API key and secret to access your B2BinPay account:

```php
$provider = new B2Binpay\Provider(
    'API_KEY',
    'API_SECRET'
);
``` 

#### Test Mode

In order to use testing sandbox, pass `true` as a third parameter for `B2Binpay\Provider`:

```php
$provider = new B2Binpay\Provider(
    'API_KEY',
    'API_SECRET',
    true
);
``` 

**Warning:** Sandbox and main gateway have their own pairs of key and secret!

### Create a bill

_The payment currency is considered to match the currency of your wallet_.

Create a new bill:

```php
$bill = $provider->createBill(  
        'WALLET_ID',
        'AMOUNT',
        'CURRENCY',
        'LIFETIME',
        'TRACKING_ID',
        'CALLBACK_URL',
        'SUCCESS_URL',
        'ERROR_URL'
  );
```

| Params | Description |
| --- | --- |
| WALLET_ID | (int) Each wallet is responsible for creating bill only in the currency assigned to it |
| AMOUNT | (string) Amount in the value of the currency being created |
| CURRENCY | (string) See list of supported currencies in the table above |
| LIFETIME | (int) Number in seconds that will set bill to expire from the current creation date |
| TRACKING_ID | (string) _Optional_. Track for bill tracking. This value will be returned on callback |
| CALLBACK_URL | (string) _Optional_. URL to which the callback will be sent |
| SUCCESS_URL | (string) _
Optional_. URL to which the user can be sent after successful payment, is used only on the payment page |
| ERROR_URL | (string) _
Optional_. URL to which the user can be sent after unsuccessful payment, is used only on the payment page |

### Convert currency

You can get actual rates and convert supported currencies respecting your wallet's parameters.

Get rates for _BASE_CURRENCY_:

```php
$rates = $provider->getRates('BASE_CURRENCY', 'RATE_TYPE');
```

| Params | Description |
| --- | --- |
| BASE_CURRENCY | (string) *Optional*. Currency to which the rates will be calculated. Default: USD |
| RATE_TYPE | (string) *Optional*. Receiving the type of rates, for **deposit** and **withdrawal**. Default: deposit  |

Convert currency using actual rates:

```php
$amount = $provider->convertCurrency('AMOUNT', 'BASE_CURRENCY', 'CURRENCY', $rates);
```

| Params | Description |
| --- | --- |
| AMOUNT | (string) The amount in the currency to be converted |
| BASE_CURRENCY | (string) The currency in which the amount is indicated  |
| CURRENCY | (string) Currency in which you want to convert the amount |
| $rates | (array) _
Optional_. Current rates. If the parameter is not specified, then the rates will be requested again |

Now you can provide `$amount` variable as a second parameter for `createBill()` method to set an accurate amount of
cryptocurrency.

### Add markup

You can add some markup to the existing amount.

Set _10%_ markup for the current amount:

```php
$amount = $provider->addMarkup($amount, 'CURRENCY', 10);
```

| Params | Description |
| --- | --- |
| $amount | (string) Amount to add markup |
| CURRENCY | (string) Currency in which markup is added |
| 10 | (int) Percentage on which to add markup |

### Callback

Once bill status changed, our server can send a callback to your configured Callback URL. Also, you can specify Tracking
ID, which will return with the callback to identify the exact order. To do that provide additional parameters
to `createBill()` method:

```php
$bill = $provider->createBill(
        'WALLET_ID',
        $amount,
        'CURRENCY',
        'LIFETIME',
        '202009051801',
        'https://my.callback.url/callback.php'
    );
```

**Warning:** If specified, your Callback URL should return the message "OK" with status 200. Until that payment will not
be considered complete!

```php
header('HTTP/1.1 200 OK');
exit('OK');
```

#### Callback verification

You can verify Callback request headers by comparing it with the `$provider->verifySign()` method output:

```php
$verifySign = $provider->verifySign($_POST['sign']['time'], $_POST['sign']['hash']);
if (!$verifySign) {
    header('HTTP/1.1 401 Unauthorized');
    exit();
}
```

****WARNING:**** for each callback `$ _POST ['sign'] ['hash']` a new one is generated - if you
received `$ _POST ['sign'] ['hash']` which was already used before, you should throw the same error as for signature
verification

#### Callback body

Bill callback request will contain the following data:

```json
{
  "data": {
    "id": BILL_ID,
    "url": URL_TO_BILL_PAYMENT_PAGE,
    "address": BLOCKCHAIN_ADDRESS,
    "created": TIME,
    "expired": TIME
    |
    NULL,
    "status": BILL_STATUS,
    "tracking_id": TRACKING_ID,
    "callback_url": URL
    |
    NULL
    "amount": AMOUNT_MULTIPLIED_BY_TEN_IN_POW,
    "actual_amount": ALREADY_PAID_AMOUNT_MULTIPLIED_BY_TEN_IN_POW,
    "pow": POW,
    "message": MESSAGE
    |
    NULL,
    "transactions": [
      {
        "id": TRANSACTION_ID,
        "bill_id": BILL_ID,
        "created": TIME,
        "amount": TRANSACTION_AMOUNT_MULTIPLIED_BY_TEN_IN_POW
        ",
        "pow": POW,
        "status": TRANSACTION_STATUS,
        "transaction": HASH_TRANSACTION_IN_BLOCKCHAIN,
        "type": 0,
        "currency": {
          "iso": ISO_CODE_CURRENCY,
          "alpha": SYMBOL_CURRENCY
        }
      }
    ],
    "currency": {
      "iso": ISO_CODE_CURRENCY,
      "alpha": SYMBOL_CURRENCY
    },
    "sign": {
      "time": TIME,
      "hash": HASH
    }
  }
}
```

Withdraw callback request will contain the following data:

```json
{
  "data": {
    "id": WITHDRAW_ID,
    "virtual_wallet_id": VIRTUAL_WALLET_ID,
    "with_fee": INCLUDE_COMMISSION_IN_WITHDRAW,
    "created": TIME,
    "address": BLOCKCHAIN_ADDRESS,
    "amount": AMOUNT_MULTIPLIED_BY_TEN_IN_POW,
    "fee": BLOCKCHAIN_FEE_MULTIPLIED_BY_TEN_IN_POW,
    "pow": POW,
    "status": WITHDRAW_STATUS,
    "transaction": HASH_TRANSACTION_IN_BLOCKCHAIN
    |
    NULL
    "tracking_id": TRACKING_ID
    |
    NULL,
    "unique_id": UNIQUE_WITHDRAW_ID,
    "callback_url": URL
    |
    NULL,
    "message": MESSAGE
    |
    NULL,
    "currency": {
      "iso": ISO_CODE_CURRENCY,
      "alpha": SYMBOL_CURRENCY
    },
    "sign": {
      "time": TIME,
      "hash": HASH
    }
  }
}
```

### Create a withdrawal

_From a virtual wallet, you can make withdrawals to any blockchain, for this you need to specify ADDRESS and CURRENCY_.

Create a new withdraw:

```php
$bill = $provider->createWithdrawal(  
        'VIRTUAL_WALLET_ID',
        'AMOUNT',
        'CURRENCY',
        'ADDRESS',
        'UNIQUE_ID',
        'TRACKING_ID',
        'CALLBACK_URL',
        'MESSAGE',
        'WITH_FEE'
    );
```

| Params | Description |
| --- | --- |
| VIRTUAL_WALLET_ID | (int) ID virtual wallet. If the currency of the virtual wallet does not match the currency in which the withdrawal is to be made, the system will automatically convert at the current rate |
| AMOUNT | (string) Amount to be withdrawn |
| CURRENCY | (string) See list of supported currencies in the table above |
| ADDRESS | (string) Blockchain address to which you want to withdraw |
| UNIQUE_ID | (int) Any unique positive number. This number should not be repeated from withdrawal to withdrawal |
| TRACKING_ID | (string) _Optional_. Track for withdraw tracking. This value will be returned on callback |
| CALLBACK_URL | (string) _Optional_. URL to which the callback will be sent |
| MESSAGE | (string) _Optional_. Used for Ripple blockchain, NEM, Stellar, EOS and Binance Chain |
| WITH_FEE | (boolean) _
Optional_. Include the commission in the withdrawal amount. Not all blockchains support this method |

## System statuses lists

### List of bills statuses

| Status | Description |
| --- | --- |
| -2 | Failed |
| -1 | Expired |
| 1 | Created |
| 2 | Paid |

### List of transactions statuses

| Status | Description |
| --- | --- |
| -4 | Waiting for return |
| -3 | Returned |
| -2 | Failed |
| -1 | Expired |
| 0 | Pending |
| 1 | Sent |
| 2 | Approved |

### List of withdraws statuses

| Status | Description |
| --- | --- |
| -2 | Failed |
| 0 | Waiting |
| 1 | Pending |
| 2 | Sent |

### List of transfers statuses

| Status | Description |
| --- | --- |
| -1 | Failed |
| 0 | Pending |
| 1 | Sent |

### List of transfers types

| Status | Description |
| --- | --- |
| 0 | Deposit from blockchain |
| 1 | Bank transfer |
| 2 | Auto withdraw |
| 3 | Withdraw blockchain fee |
| 4 | Token payout fee |
| 5 | Finance deposit |
| 6 | Bank transfer commission |
| 7 | Commissions |

## License

B2BinPay\API-PHP is licensed under the [MIT License](https://github.com/b2binpay/api-php/blob/master/LICENSE).
