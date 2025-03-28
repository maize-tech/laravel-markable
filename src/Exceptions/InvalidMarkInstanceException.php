<?php

namespace Maize\Markable\Exceptions;

use Exception;
use Throwable;

class InvalidMarkInstanceException extends Exception
{
    public function __construct($message = 'Must be a Mark instance', $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function create(): self
    {
        return new self;
    }
}
