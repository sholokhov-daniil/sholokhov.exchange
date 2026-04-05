<?php

namespace Sholokhov\Exchange\Source;

use Iterator;
use EmptyIterator;

/**
 * @implements Iterator
 *
 * @package Source
 */
trait IterableTrait
{
    protected ?Iterator $iterator = null;

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

    /**
     * Получение итератора данных
     *
     * @return Iterator
     */
    protected function getIterator(): Iterator
    {
        return $this->iterator ??= $this->load();
    }

    /**
     * Инициализация итератора данных
     *
     * @return Iterator
     */
    protected function load(): Iterator
    {
        return new EmptyIterator;
    }
}