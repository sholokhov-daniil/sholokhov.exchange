<?php

namespace Sholokhov\Exchange\Preparation\IBlock\Element;

use Exception;

use Sholokhov\Exchange\Factory\Result\SimpleFactory;
use Sholokhov\Exchange\Preparation\Base\AbstractIBlockSection;
use Sholokhov\Exchange\Preparation\IBlock\PropertyTrait;
use Sholokhov\Exchange\Target\Import\IBlock\Section;
use Sholokhov\Exchange\Fields\IBlock\ElementFieldInterface;

use Sholokhov\Exchange\ExchangeInterface;
use Sholokhov\Exchange\Fields\FieldInterface;

use Bitrix\Iblock\PropertyTable;
use Sholokhov\Exchange\Target\Options\Import\IBlock\IBlockOption;

/**
 * Преобразует значение имеющего связь к элементу информационного блока
 *
 * Если элемент будет отсутствовать, то будет произведено автоматическое создание
 *
 * @package Preparation
 */
class IBlockSection extends AbstractIBlockSection
{
    use PropertyTrait;

    /**
     * @param int $iblockId Информационный блок, которому относится свойство хранения значения
     * @param string $primary Ключ по которому будет производиться проверка уникальности
     */
    public function __construct(int $iblockId, string $primary = 'XML_ID')
    {
        $this->iblockId = $iblockId;
        parent::__construct($primary);
    }

    /**
     * Инициализация импорта элементов информационного блока
     *
     * @param FieldInterface $field Свойство в которое производится преобразование
     * @return ExchangeInterface
     *
     * @throws Exception
     */
    protected function getTarget(FieldInterface $field): ExchangeInterface
    {
        $property = $this->getPropertyRepository()->get($field->getTo());
        $options = new IBlockOption($property['LINK_IBLOCK_ID']);
        $exchange = new Section($options);

        return $exchange->setResultRepositoryFactory(new SimpleFactory);
    }

    /**
     * Предоставляет идентификатор информационного блока в котором должен храниться элемент информационного блока
     *
     * @param FieldInterface $field Свойство из которого необходимо получить идентификатор информационного блока
     * @return int
     */
    protected function getFieldIBlockID(FieldInterface $field): int
    {
        $property = $this->getPropertyRepository()->get($field->getTo());
        return (int)($property['LINK_IBLOCK_ID'] ?? 0);
    }

    /**
     * Проверка возможности преобразовать значение свойства
     *
     * @param mixed $value Значение, которое необходимо преобразовать
     * @param FieldInterface $field Свойство, которое преобразовывается
     * @return bool
     */
    public function supported(mixed $value, FieldInterface $field): bool
    {
        return $field instanceof ElementFieldInterface
            && ($property = $this->getPropertyRepository()->get($field->getTo()))
            && $property['PROPERTY_TYPE'] === PropertyTable::TYPE_SECTION
            && !$property['USER_TYPE']
            && $property['LINK_IBLOCK_ID'];
    }
}