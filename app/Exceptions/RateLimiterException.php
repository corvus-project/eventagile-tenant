<?php

namespace App\Exceptions;

use Exception;

class RateLimiterException extends Exception
{
    public function __construct($message = "Rate limit exceeded", $code = 429)
    {
        parent::__construct($message, $code);
    }
    public function render($request)
    {
        return response()->view('pages.errors.rate-limitter', ['error' => $this->getMessage()], 429);
    }
}
