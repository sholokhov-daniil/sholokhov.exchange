<?php

namespace Sholokhov\Exchange\Preparation;

use Countable;
use Sholokhov\Exchange\Fields\FieldInterface;

/**
 * Цепочка преобразователей данных.
 * Из цепочки выбирается первый подходящий и производится модификация
 *
 * @package Preparation
 */
class Chain implements PreparationInterface, Countable
{
    /**
     * Преобразователи данных
     *
     * @var PreparationInterface[]
     */
    private array $prepares = [];

    /**
     * Преобразование значения
     *
     * @param mixed $value
     * @param FieldInterface $field
     * @return mixed
     */
    public function prepare(mixed $value, FieldInterface $field): mixed
    {
        return ($prepared = $this->getSupported($value, $field)) ? $prepared->prepare($value, $field) : $value;
    }

    /**
     * Преобразователь поддерживается
     *
     * @param mixed $value
     * @param FieldInterface $field
     * @return bool
     */
    public function supported(mixed $value, FieldInterface $field): bool
    {
        return $this->getSupported($value, $field) !== null;
    }

    /**
     * Количество преобразователей
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->prepares);
    }

    /**
     * Добавление преобразователя
     *
     * @param PreparationInterface $prepare
     * @return PreparationInterface
     */
    public function add(PreparationInterface $prepare): PreparationInterface
    {
        array_unshift($this->prepares, $prepare);
        return $this;
    }

    /**
     * Добавление списка преобразователей
     *
     * @param iterable $iterator
     * @return PreparationInterface
     */
    public function addList(iterable $iterator): PreparationInterface
    {
        foreach ($iterator as $entity) {
            $this->add($entity);
        }

        return $this;
    }

    /**
     * Получение преобразователя, который поддерживает свойство
     *
     * @param mixed $value
     * @param FieldInterface $field
     * @return PreparationInterface|null
     */
    private function getSupported(mixed $value, FieldInterface $field): ?PreparationInterface
    {
        foreach ($this->prepares as $prepare) {
            if ($prepare->supported($value, $field)) {
                return $prepare;
            }
        }

        return null;
    }
}