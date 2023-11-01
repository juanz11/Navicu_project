<?php

namespace HesperiaPlugins\Stripe\Classes\lib\Error;

class SignatureVerification extends Base
{
    public function __construct(
        $message,
        $sigHeader,
        $httpBody = null
    ) {
        parent::__construct($message, null, $httpBody, null, null);
        $this->sigHeader = $sigHeader;
    }

    public function getSigHeader()
    {
        return $this->sigHeader;
    }
}
