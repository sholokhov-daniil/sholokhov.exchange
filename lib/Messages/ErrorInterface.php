<?php

namespace Sholokhov\Exchange\Messages;

use Stringable;

/**
 * Описание ошибки
 *
 * @package Message
 */
interface ErrorInterface extends Stringable
{
    /**
     * Получение кода ошибки
     *
     * @return int
     */
    public function getCode(): int;

    /**
     * Текстовое сообщение ошибки
     *
     * @return string
     */
    public function getMessage(): string;

    /**
     * Получение контекста ошибки
     *
     * @return mixed
     */
    public function getContext(): mixed;
}