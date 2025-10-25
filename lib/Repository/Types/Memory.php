<?php

namespace Sholokhov\Exchange\Repository\Types;

use Sholokhov\Exchange\Repository\RepositoryInterface;

/**
 * Базовое представление контейнера.
 *
 * @internal
 * @implements RepositoryInterface
 * @package Repository
 */
class Memory implements RepositoryInterface
{
    /**
     * Хранимые значения.
     *
     * @var array
     */
    protected array $fields = [];

    /**
     * @param array $fields
     */
    public function __construct(array $fields = [])
    {
        array_walk(
            $fields,
            fn($value, $key) => $this->set($key, $value)
        );
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->fields;
    }

    /**
     * Количество записей в контейнере.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->fields);
    }

    /**
     * Указание значения.
     *
     * @param string $id
     * @param mixed $value
     * @return void
     */
    public function set(string $id, mixed $value): void
    {
        $this->fields[$id] = $value;
    }

    /**
     * Получение значения свойства.
     *
     * @param string $id
     * @param mixed|null $default
     * @return mixed
     */
    public function get(string $id, mixed $default = null): mixed
    {
        return array_key_exists($id, $this->fields) ? $this->fields[$id] : $default;
    }

    /**
     * Получение текущего значения.
     *
     * @return mixed
     */
    public function current(): mixed
    {
        return current($this->fields);
    }

    /**
     * Передвинуть указатель вперед.
     *
     * @return void
     */
    public function next(): void
    {
        next($this->fields);
    }

    /**
     * Получение текущего ключа.
     *
     * @return string|int|null
     */
    public function key(): string|int|null
    {
        return key($this->fields);
    }

    /**
     * Проверка корректного положения каретки.
     *
     * @return bool
     */
    public function valid(): bool
    {
        return array_key_exists($this->key(), $this->fields);
    }

    /**
     * Передвинуть каретку в начало списка.
     *
     * @return void
     */
    public function rewind(): void
    {
        reset($this->fields);
    }

    /**
     * Проверка наличия свойства.
     *
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool
    {
        return array_key_exists($id, $this->fields);
    }

    /**
     * Удаление значения
     *
     * @param string $id
     * @return void
     */
    public function delete(string $id): void
    {
        unset($this->fields[$id]);
    }

    /**
     * Очистить хранилище
     *
     * @return void
     */
    public function clear(): void
    {
        $this->fields = [];
    }
}