<?php

namespace Maize\Markable\Exceptions;

use Exception;
use Throwable;

class InvalidMarkValueException extends Exception
{
    public function __construct($message = 'The given mark value is not allowed.', $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function create(): self
    {
        return new self;
    }
}
