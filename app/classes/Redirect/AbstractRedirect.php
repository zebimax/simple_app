<?php

namespace Redirect;


abstract class AbstractRedirect implements ValidateRedirectInterface
{
    protected function redirect($url)
    {
        header("HTTP/1.0 301 Moved Permanently");
        header("Location: $url");
    }
}