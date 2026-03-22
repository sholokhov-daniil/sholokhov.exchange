<?php

namespace Sholokhov\Exchange\Source;

use Iterator;
use ArrayIterator;

/**
 * Источник данных на основе сериализованной строки
 *
 * @package Source
 */
class SerializeItem implements Iterator
{
    use IterableTrait;

    /**
     * @param string $data Строка с данными
     * @param bool $multiple Данные являются множественными
     */
    public function __construct(
        private readonly string $data,
        private readonly bool $multiple = true,
    )
    {
    }

    /**
     * Инициализация итератора данных из сериализованной строки
     *
     * @return Iterator
     */
    protected function load(): Iterator
    {
        $data = @unserialize($this->data);
        return $this->multiple && is_array($data) ? new ArrayIterator($data) : new ArrayIterator([$data]);
    }
}