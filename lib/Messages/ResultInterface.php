<?php

namespace Sholokhov\Exchange\Messages;

/**
 * @package Message
 */
interface ResultInterface
{
    /**
     * Успешный результат (отсутствие ошибок)
     *
     * @return bool
     */
    public function isSuccess(): bool;

    /**
     * Добавление ошибки
     *
     * @param ErrorInterface $error
     * @return $this
     */
    public function addError(ErrorInterface $error): self;

    /**
     * Добавление ошибок
     *
     * @param ErrorInterface[] $errors
     * @return $this
     */
    public function addErrors(array $errors): self;

    /**
     * Указание нового списка ошибок (старые будут удалены)
     *
     * @param ErrorInterface[] $errors
     * @return $this
     */
    public function setErrors(array $errors): self;

    /**
     * Получение всех ошибок
     *
     * @return ErrorInterface[]
     */
    public function getErrors(): array;

    /**
     * Получение ошибочных сообщений
     *
     * @return string[]
     */
    public function getErrorMessages(): array;

    /**
     * Получение ошибки по коду
     *
     * @param string $code
     * @return ErrorInterface|null
     */
    public function getErrorByCode(string $code): ?ErrorInterface;
}