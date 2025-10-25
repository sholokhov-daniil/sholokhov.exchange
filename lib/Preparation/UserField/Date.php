<?php

namespace Sholokhov\Exchange\Preparation\UserField;

use Sholokhov\Exchange\Fields\FieldInterface;
use Sholokhov\Exchange\Preparation\Base\AbstractDate;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Преобразование значения в дату @see \Bitrix\Main\Type\Date
 *
 * @package Preparation
 */
class Date extends AbstractDate implements LoggerAwareInterface
{
    use UFTrait, LoggerAwareTrait;

    /**
     * @param string $entityId ID сущности в рамках которого производится преобразование
     */
    public function __construct(string $entityId)
    {
        $this->entityId = $entityId;
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
        $property = $this->getFieldRepository()->get($field->getTo());
        return $property && $property['USER_TYPE_ID'] === 'date';
    }
}