<?php

namespace Sholokhov\Exchange\Preparation\UserField;

use CFile;

use Sholokhov\Exchange\Fields\FieldInterface;
use Sholokhov\Exchange\Preparation\AbstractPrepare;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerAwareInterface;

/**
 * Преобразование значение в файл, который воспринимает пользовательское свойство (UF)
 *
 * @package Preparation
 */
class File extends AbstractPrepare implements LoggerAwareInterface
{
    use LoggerAwareTrait, UFTrait;

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
        return $property && $property['USER_TYPE_ID'] === 'file';
    }

    /**
     * Логика преобразования значения
     *
     * @param mixed $value
     * @param FieldInterface $field
     * @return array|null
     */
    protected function logic(mixed $value, FieldInterface $field): ?array
    {
        if (empty($value)) {
            return null;
        }

        return CFile::MakeFileArray($value) ?: null;
    }
}