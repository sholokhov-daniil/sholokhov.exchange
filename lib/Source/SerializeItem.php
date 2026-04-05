<?php

namespace Sholokhov\Exchange\Source;

use Iterator;
use ArrayIterator;

use Sholokhov\Exchange\Reader\DataReaderInterface;
use Sholokhov\Exchange\Exception\Reader\ReaderException;

/**
 * Источник данных на основе сериализованной строки
 *
 * @final
 * @package Source
 */
final class SerializeItem implements Iterator
{
    use IterableTrait;

    /**
     * @param DataReaderInterface $reader Провайдер данных
     * @param bool $multiple Данные являются множественными
     */
    public function __construct(
        private readonly DataReaderInterface $reader,
        private readonly bool $multiple = true,
    )
    {
    }

    /**
     * Инициализация итератора данных из сериализованной строки
     *
     * @return Iterator
     * @throws ReaderException
     */
    protected function load(): Iterator
    {
        $resource = $this->reader->read();
        $data = stream_get_contents($resource, -1, 0);
        fclose($resource);

        $data = @unserialize($data);

        return $this->multiple && is_array($data) ? new ArrayIterator($data) : new ArrayIterator([$data]);
    }
}