<?php

namespace B2Binpay\Exception;

class IncorrectRatesException extends B2BinpayException
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
