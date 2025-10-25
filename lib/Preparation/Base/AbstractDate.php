<?php

namespace Sholokhov\Exchange\Preparation\Base;

use DateTime;

use Exception;
use Sholokhov\Exchange\Fields\FieldInterface;
use Sholokhov\Exchange\Preparation\AbstractPrepare;

use Bitrix\Main\Type\Date as BxDate;

/**
 * Приведение значения к объекту @see BxDate
 *
 * @package Preparation
 */
abstract class AbstractDate extends AbstractPrepare
{
    /**
     * Преобразование значения
     *
     * @param mixed $value
     * @param FieldInterface $field
     * @return BxDate|string
     * @throws Exception
     */
    protected function logic(mixed $value, FieldInterface $field): BxDate|string
    {
        return $value ? BxDate::createFromPhp(new DateTime($value)) : '';
    }
}