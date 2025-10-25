<?php

namespace Sholokhov\Exchange\Preparation\IBlock\Element;

use Sholokhov\Exchange\Fields\FieldInterface;
use Sholokhov\Exchange\Preparation\Base\AbstractDate;
use Sholokhov\Exchange\Preparation\IBlock\PropertyTrait;
use Sholokhov\Exchange\Fields\IBlock\ElementFieldInterface;

use Bitrix\Iblock\PropertyTable;

/**
 * Приведение значения к типу @see \Bitrix\Main\Type\Date
 *
 * @package Preparation
 */
class Date extends AbstractDate
{
    use PropertyTrait;

    /**
     * @param int $iBlockID ИБ в рамках которого производится преобразование
     */
    public function __construct(int $iBlockID)
    {
        $this->iblockId = $iBlockID;
    }

    /**
     * Проверка поддержки свойства
     *
     * @param mixed $value Значение, которое необходимо преобразовать
     * @param FieldInterface $field Свойство, которое преобразовывается
     * @return bool
     */
    public function supported(mixed $value, FieldInterface $field): bool
    {
        $code = $field->getTo();

        if ($field instanceof ElementFieldInterface) {
            $property = $this->getPropertyRepository()->get($code);
            return $property && $property['USER_TYPE'] === PropertyTable::USER_TYPE_DATE;
        }

        return $code === 'DATE_ACTIVE_FROM' || $code === 'DATE_ACTIVE_TO' || $code === 'TIMESTAMP_X';
    }
}