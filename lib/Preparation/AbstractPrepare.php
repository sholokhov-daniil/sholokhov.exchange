<?php

namespace Sholokhov\Exchange\Preparation;

use Sholokhov\Exchange\Fields\FieldInterface;

/**
 * Базовый класс преобразователей данных
 *
 * @package Preparation
 */
abstract class AbstractPrepare implements PreparationInterface
{
    /**
     * Преобразование значения
     *
     * @param mixed $value Значение, которое необходимо преобразовать
     * @param FieldInterface $field Свойство, которое преобразовывается
     * @return mixed
     */
    abstract protected function logic(mixed $value, FieldInterface $field): mixed;

    /**
     * Преобразование значения
     *
     * @param mixed $value Значение, которое необходимо преобразовать. Может принимать массив
     * @param FieldInterface $field Свойство, которое необходимо преобразовать
     * @return mixed
     */
    public function prepare(mixed $value, FieldInterface $field): mixed
    {
        if (is_array($value)) {
            $result = array_map(fn($chit) => $this->logic($chit, $field), array_filter($value));
            $result = array_filter($result);
        } else {
            $result = $this->logic($value, $field);
        }

        return $this->after($result, $field);
    }

    /**
     * Нормализация конечного значения
     *
     * Вызывается после нормализации значения(-ий).
     * Метод создан для переопределения потомками
     *
     * @param mixed $value
     * @param FieldInterface $field
     * @return mixed
     */
    protected function after(mixed $value, FieldInterface $field): mixed
    {
        return $value;
    }
}