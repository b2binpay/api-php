<?php

namespace B2Binpay\Exception;

class EmptyResponseException extends B2BinpayException
{
    public function __construct($url)
    {
        parent::__construct("Server returned an empty response calling $url");
    }
}
