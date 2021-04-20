## Setup and run
- Set **AUTH_KEY** and **AUTH_SECRET** in `.env` 
- Set **BTC**, **ETH** and **USDT-ETH** in `.env` to match your wallet IDs
- Set **VW_ID** and **VW_CURRENCY** in `.env` to match your virtual wallet config

Run in this folder:
```bash
php -S localhost:8000
```
then open url in your browser:
```
http://localhost:8000/createBill.php
```
response:
```
stdClass Object
(
    [id] => 18205
    [url] => https://gw-test.b2binpay.com/eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJCMkJDcnlwdG9QYXkiLCJzdWIiOjE4MjA1LCJpYXQiOjE2MDIwNjM5MDJ9.XD36a2jVxlc6LNmC5nUwZvoezFVoO9T2PtKasIkO5Mo
    [address] => 5f7d8e1eb8e6cccbd8ca962b80445df1f7f38c57759f0
    [created] => 2020-10-01 15:45:02
    [expired] => 
    [status] => 1
    [tracking_id] => 1602063901
    [callback_url] =>
    [success_url] =>
    [error_url] =>
    [amount] => 1099353992000000000000
    [actual_amount] => 0
    [pow] => 18
    [transactions] => Array
        (
        )

    [currency] => stdClass Object
        (
            [iso] => 2005
            [alpha] => USDT
        )

    [message] => 
)
```
