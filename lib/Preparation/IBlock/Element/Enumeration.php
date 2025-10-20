<?php

namespace Sholokhov\Exchange\Preparation\IBlock\Element;

use Exception;

use Sholokhov\Exchange\ExchangeInterface;
use Sholokhov\Exchange\Factory\Result\SimpleFactory;
use Sholokhov\Exchange\Fields\FieldInterface;
use Sholokhov\Exchange\Fields\IBlock\ElementFieldInterface;
use Sholokhov\Exchange\Preparation\IBlock\PropertyTrait;
use Sholokhov\Exchange\Preparation\Base\AbstractEnumeration;
use Sholokhov\Exchange\Target\Import\IBlock\Property\PropertyEnumeration;

use Bitrix\Iblock\PropertyTable;
use Sholokhov\Exchange\Target\Options\Import\IBlock\PropertyEnumerationOption;

/**
 * Производит импорт значения списка и преобразовывает значение в идентификатор значения списка
 *
 * @package Preparation
 */
class Enumeration extends AbstractEnumeration
{
    use PropertyTrait;

    /**
     * @param int $iBlockID ИБ в рамках которого производится преобразование
     */
    public function __construct(int $iBlockID, string $primary = 'VALUE')
    {
        $this->iblockId = $iBlockID;
        parent::__construct($primary);
    }

    /**
     * Инициализация импорта элементов списка
     *
     * @param FieldInterface $field Свойство, которое преобразовывается
     * @return ExchangeInterface
     * @throws Exception
     */
    protected function getTarget(FieldInterface $field): ExchangeInterface
    {
        $options = new PropertyEnumerationOption($this->iblockId, $field->getTo());
        $exchange = new PropertyEnumeration($options);

        return $exchange->setResultRepositoryFactory(new SimpleFactory);
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
            && $property['PROPERTY_TYPE'] === PropertyTable::TYPE_LIST
            && !$property['USER_TYPE'];
    }
}