<?php

namespace Sholokhov\Exchange\Exception;

use Exception;

/**
 * Основное исключение обмена
 */
class ExchangeException extends Exception
{
    /**
     * Контекст исключения
     *
     * @var mixed|null
     */
    private mixed $context = null;

    /**
     * Получение контекста исключения
     *
     * @return mixed
     */
    public function getContext(): mixed
    {
        return $this->context;
    }

    /**
     * Установка контекста исключения
     *
     * @param mixed $context
     * @return $this
     */
    public function setContext(mixed $context): ExchangeException
    {
        $this->context = $context;
        return $this;
    }
}