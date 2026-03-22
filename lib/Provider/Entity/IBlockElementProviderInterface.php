<?php

namespace Sholokhov\Exchange\Provider\Entity;

use CIBlockResult;

interface IBlockElementProviderInterface
{
    /**
     * @return CIBlockResult|null
     */
    public function query(): ?CIBlockResult;

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

    /**
     * Установка смещения запроса
     *
     * @param int $offset
     *
     * @return $this
     */
    public function setOffset(int $offset): static;
}