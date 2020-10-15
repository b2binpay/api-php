<?php
require_once __DIR__ . '/config.php';

// Callback with test data for the bill
$bill_callback_test = '{
  "id": "17282",
  "url": "https://gw-test.b2binpay.com/eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCMkJDcnlwdG9QYXkiLCJzdWIiOjE3MjgyLCJpYXQiOjE1OTk4MzM3MDN9.DTl8y50rQ3lmazkJIkMk3JAFo-TGt7_0QH5p_4ICSyQ",
  "address": "5f5b85106635aa26398dca6f47b49876cbaffbc9954f9",
  "created": "2020-09-11 14:15:03",
  "expired": "",
  "status": "2",
  "tracking_id": "1599833360",
  "callback_url": "https://my.callback.url/callback.php",
  "amount": "7000000000000000000",
  "actual_amount": "46000000000000000000",
  "pow": "18",
  "message": "",
  "transactions": [
    {
      "id": "15514",
      "bill_id": "17282",
      "created": "2020-09-12 02:30:04",
      "amount": "6500000000000000000",
      "pow": "18",
      "status": "2",
      "transaction": "NWY1Yjg1MTA2NjM1YWEyNjM5OGRjYTZmNDdiNDk4NzZjYmFmZmJjOTk1NGY5IzYuNQ",
      "type": "0",
      "currency": {
        "iso": "2005",
        "alpha": "USDT"
      }
    },
    {
      "id": "15512",
      "bill_id": "17282",
      "created": "2020-09-11 22:15:03",
      "amount": "19500000000000000000",
      "pow": "18",
      "status": "2",
      "transaction": "NWY1Yjg1MTA2NjM1YWEyNjM5OGRjYTZmNDdiNDk4NzZjYmFmZmJjOTk1NGY5IzE5LjU",
      "type": "0",
      "currency": {
        "iso": "2005",
        "alpha": "USDT"
      }
    }
  ],
  "currency": {
    "iso": "2005",
    "alpha": "USDT"
  },
  "sign": {
    "time": "Fri Oct 02 2020 12:00:19 GMT+0000",
    "hash": "{HASH}"
  }
}';

// Callback with test data for the withdrawal
$withdrawal_callback_test = '{
  "id": "1062",
  "virtual_wallet_id": "45",
  "with_fee": "0",
  "created": "2020-09-11 12:11:11",
  "address": "5f7727d56ec77f8151fdd6026f82036ab63052b97505b",
  "amount": "112340000",
  "fee": "4000000",
  "pow": "8",
  "status": "2",
  "transaction": "trx5f5b69928ddea9813b270ed0288e7c0388f0fd4ec68f5",
  "unique_id": "1599826271",
  "callback_url": "https://my.callback.url/callback.php",
  "currency": {
    "iso": "1000",
    "alpha": "BTC"
  },
  "sign": {
    "time": "Fri Oct 02 2020 13:15:09 GMT+0000",
    "hash": "{HASH}"
  }
}';

// Set data for callback $bill_callback_test or $withdrawal_callback_test
$callback_test = $bill_callback_test;
$callback_test = json_decode($callback_test, true);


///// <-- This code simulates generation of a signature by the server, DO NOT USE IN PROD
$signString = getAuthKey() . ":" . getAuthSecret() . ":" . $callback_test['sign']['time'];
$callback_test['sign']['hash'] = password_hash($signString, PASSWORD_BCRYPT);
///// END -->


// If there is POST data, take them into work, if not, then demo
$callback = (!empty($_POST)) ? $_POST : $callback_test;

$json_string = json_encode($callback);
// Write it to 'callback.txt'
file_put_contents(
    'callback.txt',
    date('Y/M/d H:i:s') . PHP_EOL . $json_string . PHP_EOL . PHP_EOL,
    FILE_APPEND | LOCK_EX
);

// Create B2Binpay Provider object
$provider = new B2Binpay\Provider(
    getAuthKey(),
    getAuthSecret(),
    true // sandbox
);

$verify_sign = $provider->verifySign($callback['sign']['time'], $callback['sign']['hash']);

// A signature for each callback is generated every time a new one, a unique signature must be verified
if (!$verify_sign) {
    header('HTTP/1.1 401 Unauthorized');
    exit();
}

// Logic for adding a deposit or updating the withdrawal status
// IMPORTANT! Callback can be sent again, you should be prepared for this case and not add the transaction again

// If this is a callback to withdrawal
if (!empty($callback['virtual_wallet_id'])) {
    // $hash = $callback['transaction'];
    // ...

// If not, then this is a deposit
} else {
    // foreach ($callback['transactions'] AS $trx) {
    // $hash = $trx['transaction'];
    // ...
    // }
}

// Body of the response must always contain the value OK and HTTP-code 200
header('HTTP/1.1 200 OK');
echo 'OK';
