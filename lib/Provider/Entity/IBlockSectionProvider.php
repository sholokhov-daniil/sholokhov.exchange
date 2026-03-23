<?php

namespace Sholokhov\Exchange\Provider\Entity;

use CIBlockResult;
use CIBlockSection;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;

/**
 * Провайдер разделов информационного блока (IBlock).
 *
 * Обеспечивает удобный интерфейс для конфигурации запроса к элементам ИБ
 * через сеттеры фильтра, сортировки, выборки полей, лимита и смещения.
 *
 * Использует Bitrix API CIBlockElement::GetList() для выполнения запроса.
 *
 * @package Provider
 */
class IBlockSectionProvider implements IBlockProviderInterface
{
    use ProviderSelectionTrait;

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
     * @return CIBlockResult|null Возвращает объект CIBlockResult или null, если запрос не удался
     */
    public function query(): ?CIBlockResult
    {
        return CIBlockSection::GetList(
            arOrder: $this->order,
            arFilter: $this->filter,
            arSelect: $this->select,
            arNavStartParams: $this->getNav(),
        ) ?: null;
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
}