<?php

namespace B2Binpay\Exception;

class UnknownValueException extends B2BinpayException
{
    public function __construct($value)
    {
        parent::__construct("Unknown value '$value' passed to method.");
    }
}
