<?php

namespace Maize\Markable\Exceptions;

use Exception;
use Throwable;

class InvalidMarkableInstanceException extends Exception
{
    public function __construct($message = 'Model must be a valid markable instance', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function create(): self
    {
        return new self();
    }
}
