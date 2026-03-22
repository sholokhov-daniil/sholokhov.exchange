<?php

namespace Sholokhov\Exchange\Provider\Entity;

use CIBlockResult;
use CIBlockElement;
use Bitrix\Main\Loader;
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
    /**
     * Фильтр для выборки элементов ИБ
     *
     * @var array
     */
    protected array $filter = [];

    /**
     * Параметры сортировки
     *
     * @var array
     */
    protected array $order = ['SORT' => 'ASC'];

    /**
     * Поля, которые нужно выбрать из элементов ИБ
     *
     * @var array
     */
    protected array $select = ['ID', 'NAME', 'IBLOCK_ID'];

    /**
     * Смещение начала выборки
     *
     * @var int
     */
    protected int $offset = 0;

    /**
     * Лимит количества выбираемых элементов
     *
     * @var int
     */
    protected int $limit = 0;

    public function __construct()
    {
        if (!Loader::includeModule('iblock')) {
            throw new LoaderException('IBLOCK module is not installed.');
        }
    }

    /**
     * Выполняет запрос к элементам ИБ с текущей конфигурацией фильтра, сортировки, выборки и навигации.
     *
     * @return CIBlockResult|null Возвращает объект CIBlockResult или null, если запрос не удался
     */
    public function query(): ?CIBlockResult
    {
        return CIBlockElement::GetList(
            arOrder: $this->order,
            arFilter: $this->filter,
            arNavStartParams: $this->getNav(),
            arSelectFields: $this->select
        ) ?: null;
    }

    /**
     * Устанавливает фильтр для запроса элементов ИБ.
     *
     * @param array $filter Ассоциативный массив фильтра Bitrix
     * @return $this
     */
    public function setFilter(array $filter): static
    {
        $this->filter = $filter;
        return $this;
    }

    /**
     * Устанавливает порядок сортировки элементов ИБ.
     *
     * @param array $order Ассоциативный массив вида ['FIELD' => 'ASC|DESC']
     * @return $this
     */
    public function setOrder(array $order): static
    {
        $this->order = $order;
        return $this;
    }

    /**
     * Устанавливает поля выборки элементов ИБ.
     *
     * @param array $select Список полей, которые нужно выбрать
     * @return $this
     */
    public function setSelect(array $select): static
    {
        $this->select = $select;
        return $this;
    }

    /**
     * Устанавливает лимит количества элементов для выборки.
     *
     * @param int $limit Максимальное количество элементов
     * @return $this
     */
    public function setLimit(int $limit): static
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Устанавливает смещение начала выборки элементов.
     *
     * @param int $offset Количество элементов, которые нужно пропустить
     * @return $this
     */
    public function setOffset(int $offset): static
    {
        $this->offset = $offset;
        return $this;
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

        if ($this->offset > 0) {
            $result['nOffset'] = $this->offset;
        }

        if ($this->limit > 0) {
            $result['nPageSize'] = $this->limit;
        }

        return $result;
    }
}