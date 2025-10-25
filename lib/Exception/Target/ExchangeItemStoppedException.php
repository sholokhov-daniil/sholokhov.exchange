<?php

namespace Sholokhov\Exchange\Exception\Target;

use Sholokhov\Exchange\Exception\ExchangeException;
use Throwable;

/**
 * Исключение означающее принудительную остановку обмена элемента.
 *
 * Как правило, исключение используется в событиях
 */
class ExchangeItemStoppedException extends ExchangeException
{
    /**
     * @param string $message
     * @param Throwable|null $throwable
     */
    public function __construct(string $message = "", Throwable $throwable = null)
    {
        parent::__construct($message, 444, $throwable);
    }
}