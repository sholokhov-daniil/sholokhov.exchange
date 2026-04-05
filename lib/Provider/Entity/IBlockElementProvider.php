<?php

namespace Sholokhov\Exchange\Provider\Entity;

use _CIBElement;
use CIBlockElement;

use Sholokhov\Exchange\Result\DB\CIBlockElementResult;

use Bitrix\Main\Loader;
use Bitrix\Main\DB\Result;
use Bitrix\Main\LoaderException;

/**
 * Провайдер элементов информационного блока (IBlock).
 *
 * Обеспечивает удобный интерфейс для конфигурации запроса к элементам ИБ
 * через сеттеры фильтра, сортировки, выборки полей, лимита и смещения.
 *
 * Использует Bitrix API CIBlockElement::GetList() для выполнения запроса.
 *
 * @package Provider
 */
class IBlockElementProvider implements IBlockElementProviderInterface
{
    use ProviderSelectionTrait;

    /**
     * Свойства элементов, которые нужно загрузить
     *
     * @var array
     */
    protected array $properties = [];

    public function __construct()
    {
        if (!Loader::includeModule('iblock')) {
            throw new LoaderException('IBLOCK module is not installed.');
        }

        $this->select = ['ID', 'NAME', 'IBLOCK_ID'];
    }

    /**
     * Выполняет запрос к элементам ИБ с текущей конфигурацией фильтра, сортировки, выборки и навигации.
     *
     * @return Result|null Возвращает объект Result или null, если запрос не удался
     */
    public function query(): ?Result
    {
        $res = CIBlockElement::GetList(
            arOrder: $this->order,
            arFilter: $this->filter,
            arNavStartParams: $this->getNav(),
            arSelectFields: $this->select
        ) ?: null;

        return $res ? new CIBlockElementResult($res, $this->normalizeItem(...)) : null;
    }

    /**
     * Нормализация элемента при его получении
     *
     * @param _CIBElement $element
     * @return array
     */
    protected function normalizeItem(_CIBElement $element): array
    {
        $item = $element->GetFields();
        $item['PROPERTIES'] = [];

        if (!empty($this->properties)) {
            $properties = $element->GetProperties();

            foreach ($this->properties as $code) {
                if (!empty($properties[$code])) {
                    $item['PROPERTIES'][$code] = $properties[$code];
                }
            }
        }

        return $item;
    }

    /**
     * Возвращает массив навигации для запроса CIBlockElement::GetList().
     *
     * Используется для задания лимита и смещения.
     *
     * @return array Массив навигации с ключами 'nPageSize' и 'nOffset'
     */
    protected function getNav(): array
    {
        $result = [];

        if ($this->limit > 0) {
            $result['nPageSize'] = $this->limit;
        }

        return $result;
    }

    /**
     * Устанавливает свойства элементов для выборки
     *
     * @param array $properties Массив кодов свойств
     *
     * @return $this
     */
    public function setProperties(array $properties = []): static
    {
        $this->properties = array_unique($properties);
        return $this;
    }

    /**
     * Добавление свойства элемента для выборки
     *
     * @param string $code
     * @return $this
     */
    public function addProperty(string $code): static
    {
        if (!in_array($code, $this->properties)) {
            $this->properties[] = $code;
        }

        return $this;
    }
}