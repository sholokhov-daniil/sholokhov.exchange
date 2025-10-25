<?php

namespace Sholokhov\Exchange\Preparation\Base;

use DateTime as PhpDateTime;

use Exception;
use Sholokhov\Exchange\Fields\FieldInterface;
use Sholokhov\Exchange\Preparation\AbstractPrepare;

use Bitrix\Main\Type\DateTime as BxDateTime;

/**
 * Приведение значения к объекту @see BxDateTime
 *
 * @package Preparation
 */
abstract class AbstractDateTime extends AbstractPrepare
{
    /**
     * Преобразование значения
     *
     * @param mixed $value Преобразуемое значение
     * @param FieldInterface $field Свойство, которому принадлежит преобразуемое значение
     * @return BxDateTime|string
     * @throws Exception
     */
    protected function logic(mixed $value, FieldInterface $field): BxDateTime|string
    {
        return $value ? BxDateTime::createFromPhp(new PhpDateTime($value)) : '';
    }
}