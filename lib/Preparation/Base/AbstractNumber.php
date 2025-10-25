<?php

namespace Sholokhov\Exchange\Preparation\Base;

use Sholokhov\Exchange\Fields\FieldInterface;
use Sholokhov\Exchange\Preparation\AbstractPrepare;

/**
 * Приводит значение к целочисленному
 *
 * Если значение не является числом, то преобразуется в null
 *
 * @package Preparation
 */
abstract class AbstractNumber extends AbstractPrepare
{
    /**
     * Преобразование значения
     *
     * @param mixed $value
     * @param FieldInterface $field
     * @return int
     */
    protected function logic(mixed $value, FieldInterface $field): mixed
    {
        return is_numeric($value) ? $value : null;
    }
}