<?php

namespace Sholokhov\Exchange\Source;

use Iterator;
use ArrayIterator;

use Sholokhov\Exchange\Helper\Helper;
use Sholokhov\Exchange\Reader\DataReaderInterface;
use Sholokhov\Exchange\Exception\Reader\ReaderException;

/**
 * Источник данных на основе json строки
 *
 * @final
 * @package Source
 */
final class Json implements Iterator
{
    use IterableTrait;

    /**
     * Конфигурация источника данных
     *
     * @var array
     */
    private readonly array $options;

    /**
     * Провайдер данных
     *
     * @var DataReaderInterface
     */
    private DataReaderInterface $reader;

    /**
     * @param DataReaderInterface $reader Провайдер данных
     * @param array $options Конфигурация источника
     */
    public function __construct(DataReaderInterface $reader, array $options = [])
    {
        $this->reader = $reader;
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

    /**
     * Загрузка данных
     *
     * @return Iterator
     * @throws ReaderException
     */
    private function load(): Iterator
    {
        $data = $this->loadData();
        return $this->isMultiple() && is_array($data) ? new ArrayIterator($data) : new ArrayIterator([$data]);
    }

    /**
     * Загрузка данных из json файла
     *
     * @return mixed
     * @throws ReaderException
     */
    private function loadData(): mixed
    {
        $resource = $this->reader->read();
        $json = stream_get_contents($resource, -1, 0);
        fclose($resource);

        if (!json_validate($json)) {
            return null;
        }

        $data = json_decode($json, true);

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
    private function getSourceKey(): string
    {
        return (string)($this->options['source_key'] ?? '');
    }
}