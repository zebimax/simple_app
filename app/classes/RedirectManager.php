<?php

use Redirect\ValidateRedirectInterface;

class RedirectManager
{
    private $validateRedirect;

    public function __construct(ValidateRedirectInterface $redirectInterface)
    {
        $this->validateRedirect = $redirectInterface;
    }

    public function validateWithRedirect($requestUri)
    {
        $this->validateRedirect->validateRedirect($requestUri);
    }
}