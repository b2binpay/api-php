<?php

namespace B2Binpay\Exception;

class ConnectionErrorException extends B2BinpayException
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
