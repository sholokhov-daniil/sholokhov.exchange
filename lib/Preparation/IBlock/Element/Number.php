<?php

namespace Sholokhov\Exchange\Preparation\IBlock\Element;

use Sholokhov\Exchange\Fields\FieldInterface;
use Sholokhov\Exchange\Preparation\Base\AbstractNumber;
use Sholokhov\Exchange\Preparation\IBlock\PropertyTrait;
use Sholokhov\Exchange\Fields\IBlock\ElementFieldInterface;

use Bitrix\Iblock\PropertyTable;
/**
 * Приведение значения свойства к целочисленному значению
 *
 * @package Preparation
 */
class Number extends AbstractNumber
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
        return $field instanceof ElementFieldInterface
            && ($property = $this->getPropertyRepository()->get($field->getTo()))
            && (!$property['USER_TYPE'] && $property['PROPERTY_TYPE'] === PropertyTable::TYPE_NUMBER);
    }
}