<?php

namespace Sholokhov\Exchange\Exception\Source;

use Throwable;

class InvalidJsonFileException extends SourceException
{
    /**
     * @param string $message
     * @param Throwable|null $throwable
     */
    public function __construct(string $message = "", ?Throwable $throwable = null)
    {
        parent::__construct($message, 300, $throwable);
    }
}