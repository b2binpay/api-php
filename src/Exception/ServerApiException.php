<?php

namespace B2Binpay\Exception;

class ServerApiException extends B2BinpayException
{
    public function __construct($message, $code, $status)
    {
        parent::__construct("Server returned error ($code) $message with $status status");
    }
}
