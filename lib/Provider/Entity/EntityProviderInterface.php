<?php

namespace Sholokhov\Exchange\Provider\Entity;

interface EntityProviderInterface
{
    /**
     * Выполнить запрос на получение данных
     *
     * @return object|null
     */
    public function query(): ?object;

    /**
     * Установка фильтра запроса
     *
     * @param array $filter
     *
     * @return $this
     */
    public function setFilter(array $filter): static;

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