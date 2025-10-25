<?php

namespace Sholokhov\Exchange\Messages\Type;

use Sholokhov\Exchange\Messages\ErrorInterface;
use Sholokhov\Exchange\Messages\ResultInterface;

/**
 * @package Message
 */
class Result implements ResultInterface
{
    /**
     * Ошибки при выполнении действия
     *
     * @var ErrorInterface[]
     */
    protected array $errors = [];

    /**
     * Работа завершилась успехом
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        return count($this->errors) === 0;
    }

    /**
     * Добавить ошибку
     *
     * @param ErrorInterface $error
     * @return static
     */
    public function addError(ErrorInterface $error): static
    {
        $this->errors[] = $error;
        return $this;
    }

    /**
     * Добавить ошибки
     *
     * @param ErrorInterface[] $errors
     * @return static
     */
    public function addErrors(array $errors): static
    {
        array_walk($errors, [$this, 'addError']);
        return $this;
    }

    /**
     * Установить ошибки
     *
     * @param ErrorInterface[] $errors
     * @return static
     */
    public function setErrors(array $errors): static
    {
        $this->errors = [];
        return $this->addErrors($errors);
    }

    /**
     * Получение ошибок
     *
     * @return ErrorInterface[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Получение ошибочных сообщений
     *
     * @return array
     */
    public function getErrorMessages(): array
    {
        return array_map(fn(ErrorInterface $error) => $error->getMessage(), $this->errors);
    }

    /**
     * Получить ошибку по коду
     *
     * @param string $code
     * @return ErrorInterface|null
     */
    public function getErrorByCode(string $code): ?ErrorInterface
    {
        foreach ($this->errors as $error) {
            if ($error->getCode() == $code) {
                return $error;
            }
        }

        return null;
    }
}