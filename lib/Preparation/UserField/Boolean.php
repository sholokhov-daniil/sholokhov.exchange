<?php

namespace Sholokhov\Exchange\Preparation\UserField;

use Sholokhov\Exchange\Fields\FieldInterface;
use Sholokhov\Exchange\Preparation\AbstractPrepare;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Преобразование значения в формат Да\Нет
 *
 * @package Preparation
 */
class Boolean extends AbstractPrepare implements LoggerAwareInterface
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
        return $property && $property['USER_TYPE_ID'] === 'boolean';
    }

    /**
     * Логика преобразования значения
     *
     * @param mixed $value Преобразуемое значение
     * @param FieldInterface $field Свойство, которому принадлежит преобразуемое значение
     * @return bool
     */
    protected function logic(mixed $value, FieldInterface $field): bool
    {
        return (bool)$value;
    }
}