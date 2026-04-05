<?php

namespace Sholokhov\Exchange\Provider\Entity;

trait ProviderSelectionTrait
{
    /**
     * Фильтр для выборки элементов сущности
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
     * Поля, которые нужно выбрать из элементов сущности
     *
     * @var array
     */
    protected array $select = ['ID'];

    /**
     * Лимит количества выбираемых элементов
     *
     * @var int
     */
    protected int $limit = 0;

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
     * Возвращает фильтр запроса
     *
     * @return array
     */
    public function getFilter(): array
    {
        return $this->filter;
    }

    /**
     * Устанавливает порядок сортировки элементов.
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
}
