<?php

namespace Sholokhov\Exchange\Messages\Type;

use Throwable;

use Sholokhov\Exchange\Messages\ErrorInterface;

/**
 * Описание ошибки
 *
 * @package Message
 */
class Error implements ErrorInterface
{
    public function __construct(
        private readonly string $message,
        private readonly int $code = 500,
        private readonly mixed $context = null
    )
    {
    }

    public function __toString(): string
    {
        return sprintf('[%s] %s', $this->getCode(), $this->getMessage());
    }

    /**
     * Создать объект на основе исключения
     *
     * @param Throwable $throwable
     * @return static
     */
    public static function createFromThrowable(Throwable $throwable): static
    {
        return new static($throwable->getMessage(), $throwable->getCode());
    }

    /**
     * Создание ошибки на основе ошибки битрикса
     *
     * @param \Bitrix\Main\Error $error
     * @return static
     */
    public static function createFromBitrix(\Bitrix\Main\Error $error): static
    {
        return new static($error->getMessage(), $error->getCode(), $error->getCustomData());
    }

    /**
     * Получение кода ошибки
     *
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * Текстовое сообщение ошибки
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Получение контекста ошибки
     *
     * @return mixed
     */
    public function getContext(): mixed
    {
        return $this->context;
    }
}