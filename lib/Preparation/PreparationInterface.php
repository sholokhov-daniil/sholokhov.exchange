<?php

namespace Sholokhov\Exchange\Preparation;

use Sholokhov\Exchange\Fields\FieldInterface;

/**
 * @package Preparation
 */
interface PreparationInterface
{
    /**
     * Преобразование значения
     *
     * @param mixed $value
     * @param FieldInterface $field
     * @return mixed
     */
    public function prepare(mixed $value, FieldInterface $field): mixed;

    /**
     * Преобразование поддерживает свойство и значение
     *
     * @param mixed $value
     * @param FieldInterface $field
     * @return bool
     */
    public function supported(mixed $value, FieldInterface $field): bool;
}