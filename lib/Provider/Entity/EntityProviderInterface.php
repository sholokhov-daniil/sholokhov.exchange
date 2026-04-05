<?php

namespace Sholokhov\Exchange\Provider\Entity;

use Bitrix\Main\DB\Result;

interface EntityProviderInterface
{
    /**
     * Выполнить запрос на получение данных
     *
     * @return Result|null
     */
    public function query(): ?Result;

    /**
     * Установка фильтра запроса
     *
     * @param array $filter
     *
     * @return $this
     */
    public function setFilter(array $filter): static;

    /**
     * Возвращает фильтр запроса
     *
     * @return array
     */
    public function getFilter(): array;

    /**
     * Установка порядка сортировки
     *
     * @param array $order
     *
     * @return $this
     */
    public function setOrder(array $order): static;

    /**
     * Установка полей выборки
     *
     * @param array $select
     *
     * @return $this
     */
    public function setSelect(array $select): static;

    /**
     * Установка лимита запроса
     *
     * @param int $limit
     *
     * @return $this
     */
    public function setLimit(int $limit): static;
}