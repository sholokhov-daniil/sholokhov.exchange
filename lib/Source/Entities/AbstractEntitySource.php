<?php

namespace Sholokhov\Exchange\Source\Entities;

use Iterator;

/**
 * Источник данных основан на основе элементов сущности
 *
 * Поддерживает ленивую загрузку данных по батчам с фильтрацией, выборкой полей.
 * Реализует интерфейс Iterator для удобной итерации через foreach.
 *
 * @package Source
 */
abstract class AbstractEntitySource implements Iterator
{
    /**
     * Размер пакета данных (батч)
     *
     * @var int
     */
    protected int $limit = 2000;

    /**
     * Последний обработанный ID (для батчевой загрузки)
     *
     * @var int
     */
    protected int $lastId = 0;

    /**
     * Фильтр для выборки элементов ИБ
     *
     * @var array
     */
    protected array $filter = [];

    /**
     * Поля для выборки
     *
     * @var array
     */
    protected array $select = ['ID'];

    /**
     * Текущий батч элементов
     *
     * @var array
     */
    protected array $batch = [];

    /**
     * Признак окончания итерации
     *
     * @var bool
     */
    protected bool $finished = false;

    /**
     * Загружает следующий батч элементов сущности
     *
     * @return void
     */
    abstract protected function fetchBatch(): void;

    /**
     * Устанавливает лимит элементов на один батч
     *
     * @param int $limit
     *
     * @return $this
     */
    public function setLimit(int $limit): static
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Устанавливает фильтр для выборки элементов
     *
     * @param array $filter Ассоциативный массив фильтра Bitrix
     *
     * @return $this
     */
    public function setFilter(array $filter): static
    {
        $this->filter = $filter;
        $this->rewind();
        return $this;
    }

    /**
     * Устанавливает поля выборки элементов
     *
     * @param array $select Список полей
     *
     * @return $this
     */
    public function setSelect(array $select): static
    {
        $this->select = $select;

        if (!in_array('ID', $this->select)) {
            $this->select[] = 'ID';
        }

        return $this;
    }

    /**
     * Возвращает текущий элемент итератора
     *
     * @return mixed
     */
    public function current(): mixed
    {
        return current($this->batch);
    }

    /**
     * Переводит итератор на следующий элемент
     *
     * Если текущий батч закончился, подгружает следующий батч.
     *
     * @return void
     */
    public function next(): void
    {
        next($this->batch);

        if (!$this->valid()) {
            $this->fetchBatch();
        }
    }

    /**
     * Возвращает ключ текущего элемента итератора
     *
     * @return mixed
     */
    public function key(): mixed
    {
        return key($this->batch);
    }

    /**
     * Проверяет валидность текущего элемента
     *
     * @return bool true, если элемент существует, false — если батч закончился
     */
    public function valid(): bool
    {
        $key = $this->key();
        return $key !== null && $key !== false;
    }

    /**
     * Сбрасывает итератор
     *
     * Перезапускает итерацию с самого начала
     *
     * @return void
     */
    public function rewind(): void
    {
        $this->lastId = 0;
        $this->finished = false;
        $this->batch = [];
        $this->fetchBatch();
    }

    /**
     * Строит фильтр для запроса к элементам ИБ
     *
     * Добавляет условие по последнему ID для батчевой загрузки.
     *
     * @return array
     */
    protected function buildFilter(): array
    {
        return array_merge(
            $this->filter,
            ['>ID' => $this->lastId]
        );
    }
}