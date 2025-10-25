<?php

namespace Sholokhov\Exchange\Preparation\IBlock\Element;

use Sholokhov\Exchange\Factory\Highloadblock\ProviderFactory;
use Sholokhov\Exchange\Factory\Result\SimpleFactory;
use Sholokhov\Exchange\Target\Import\Highloadblock\Element;
use Sholokhov\Exchange\Preparation\Base\AbstractIBlockImport;
use Sholokhov\Exchange\Fields\IBlock\ElementFieldInterface;

use Sholokhov\Exchange\ExchangeInterface;
use Sholokhov\Exchange\Fields\FieldInterface;

use Bitrix\Main\LoaderException;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\SystemException;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Highloadblock\HighloadBlockTable as HLT;
use Sholokhov\Exchange\Target\Options\Import\HlElementOption;

/**
 * Преобразует значение имеющего связь к элементу справочника
 *
 * Если элемент будет отсутствовать, то будет произведено автоматическое создание
 *
 * @package Preparation
 */
class HandbookElement extends AbstractIBlockImport
{
    private array $cache = [];

    /**
     * Связующий ключ по умолчанию
     *
     * @var string
     */
    protected string $defaultPrimary = 'UF_XML_ID';

    /**
     * Инициализация импорта элементов информационного блока
     *
     * @param FieldInterface $field Свойство в которое производится преобразование
     * @return ExchangeInterface
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected function getTarget(FieldInterface $field): ExchangeInterface
    {
        $property = $this->getPropertyRepository()->get($field->getTo());

        $result = HLT::query()
            ->where('TABLE_NAME', $property['USER_TYPE_SETTINGS']['TABLE_NAME'])
            ->setCacheTtl(36000)
            ->addSelect('ID')
            ->exec()
            ->fetch();

        $options = new HlElementOption($result['ID']);
        $exchange = new Element($options);

        return $exchange->setResultRepositoryFactory(new SimpleFactory);
    }

    /**
     * Нормализация результата импорта значения
     *
     * @param mixed $value
     * @param FieldInterface $field
     * @return mixed
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws LoaderException
     */
    protected function normalize(mixed $value, FieldInterface $field): mixed
    {
        if (!is_array($value) && $this->primary <> 'ID') {
            $property = $this->getPropertyRepository()->get($field->getTo());
            $provider = $this->cache[$property['USER_TYPE_SETTINGS']['TABLE_NAME']] ??= ProviderFactory::createByTable($property['USER_TYPE_SETTINGS']['TABLE_NAME']);

            $item = $provider::getRow([
                'filter' => ['ID' => $value],
                'cache' => ['ttl' => 36000]
            ]);
            if ($item) {
                $value = $item[$this->primary];
            }
        }


        return is_array($value) ? $this->normalize(reset($value), $field) : $value;
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
            && $property['USER_TYPE'] === PropertyTable::USER_TYPE_DIRECTORY
            && $property['USER_TYPE_SETTINGS']['TABLE_NAME'];
    }
}