<?php

namespace Sholokhov\Exchange\Exception\Source;

use Throwable;
use Sholokhov\Exchange\Exception\ExchangeException;

class SourceException extends ExchangeException
{
    /**
     * @param string $message
     * @param Throwable|null $throwable
     */
    public function __construct(string $message = "", int $code = 444, ?Throwable $throwable = null)
    {
        parent::__construct($message, $code, $throwable);
    }
}