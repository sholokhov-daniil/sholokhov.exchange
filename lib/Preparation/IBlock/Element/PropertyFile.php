<?php

namespace Sholokhov\Exchange\Preparation\IBlock\Element;

use CFile;

use Sholokhov\Exchange\Preparation\AbstractPrepare;
use Sholokhov\Exchange\Preparation\IBlock\PropertyTrait;
use Sholokhov\Exchange\Fields\IBlock\ElementFieldInterface;

use Sholokhov\Exchange\Fields\FieldInterface;

use Bitrix\Iblock\PropertyTable;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerAwareInterface;

/**
 * Производит преобразование пути до файла в формат, который поддерживается свойствами информационного блока
 *
 * @package Preparation
 */
class PropertyFile extends AbstractPrepare implements LoggerAwareInterface
{
    use PropertyTrait, LoggerAwareTrait;

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
            && $property['PROPERTY_TYPE'] === PropertyTable::TYPE_FILE
            && !$property['USER_TYPE'];
    }

    /**
     * Логика преобразования значения
     *
     * @param mixed $value
     * @param FieldInterface $field
     * @return mixed
     */
    protected function logic(mixed $value, FieldInterface $field): array
    {
        if (empty($value)) {
            return [];
        }

        $file = CFile::MakeFileArray($value);
        return ['VALUE' => $file, 'DESCRIPTION' => $file['name']];
    }
}