<?php

namespace Sholokhov\Exchange\Source;

use Iterator;
use ArrayIterator;
use IteratorAggregate;

use Sholokhov\Exchange\Helper\Helper;

/**
 * Источник данных на основе json строки
 *
 * @package Source
 */
class Json implements Iterator, IteratorAggregate
{
    /**
     * JSON строка
     *
     * @var string
     */
    private readonly string $json;

    /**
     * Конфигурация источника данных
     *
     * @var array
     */
    private readonly array $options;

    /**
     * @var Iterator
     */
    private Iterator $iterator;

    /**
     * @param string $json JSON строка
     * @param array $options Конфигурация источника
     */
    public function __construct(string $json, array $options = [])
    {
        $this->json = $json;
        $this->options = $options;
    }

    /**
     * Значение является множественным
     *
     * @return bool
     */
    public function isMultiple(): bool
    {
        return (bool)$this->options['multiple'];
    }

    public function current(): mixed
    {
        return $this->getIterator()->current();
    }

    public function next(): void
    {
        $this->getIterator()->next();
    }

    public function key(): mixed
    {
        return $this->getIterator()->key();
    }

    public function valid(): bool
    {
        return $this->getIterator()->valid();
    }

    public function rewind(): void
    {
        $this->getIterator()->rewind();
    }

    public function getIterator(): Iterator
    {
        return $this->iterator ??= $this->loadIterator();
    }

    /**
     * Загрузка данных
     *
     * @return Iterator
     */
    private function loadIterator(): Iterator
    {
        $data = $this->loadData();
        return $this->isMultiple() && is_array($data) ? new ArrayIterator($data) : new ArrayIterator([$data]);
    }

    /**
     * Загрузка данных из json файла
     *
     * @return mixed
     */
    private function loadData(): mixed
    {
        if (!json_validate($this->json)) {
            return null;
        }

        $data = json_decode($this->json, true);

        if (!is_array($data)) {
            return null;
        }


        $sourceKey = $this->getSourceKey();

        return $sourceKey ? Helper::getArrValueByPath($data, $sourceKey) : $data;
    }

    /**
     * Ключ в котором хранятся данные источника
     *
     * @return string
     */
    protected function getSourceKey(): string
    {
        return (string)($this->options['source_key'] ?? '');
    }
}