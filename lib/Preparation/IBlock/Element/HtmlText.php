<?php

namespace Sholokhov\Exchange\Preparation\IBlock\Element;

use Sholokhov\Exchange\Preparation\AbstractPrepare;
use Sholokhov\Exchange\Preparation\IBlock\PropertyTrait;
use Sholokhov\Exchange\Fields\IBlock\ElementFieldInterface;

use Sholokhov\Exchange\Fields\FieldInterface;

use Bitrix\Iblock\PropertyTable;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerAwareInterface;

/**
 * Преобразует значение в формат, который поддерживает HTML\Text
 *
 * @package Preparation
 */
class HtmlText extends AbstractPrepare implements LoggerAwareInterface
{
    use LoggerAwareTrait, PropertyTrait;

    /**
     * @param int $iBlockId Идентификатор информационного блока свойства в которое будет происходить запись
     */
    public function __construct(int $iBlockId)
    {
        $this->iblockId = $iBlockId;
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
            && $property['USER_TYPE'] === PropertyTable::USER_TYPE_HTML;
    }

    /**
     * Логика преобразования значения
     *
     * @param mixed $value
     * @param FieldInterface $field
     * @return array|string
     */
    protected function logic(mixed $value, FieldInterface $field): array|string
    {
        $property = $this->getPropertyRepository()->get($field->getTo());

        return $property['MULTIPLE'] === 'Y'
            ? ['VALUE' => ['TYPE' => 'HTML', 'TEXT' => $value]]
            : (string)$value;
    }

    /**
     * Обработка конечного преобразованного значения
     *
     * @param mixed $value
     * @param FieldInterface $field
     * @return mixed
     */
    protected function after(mixed $value, FieldInterface $field): mixed
    {
        return $value || $value == 0 ? $value : null;
    }
}