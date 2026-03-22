<?php

namespace Sholokhov\Exchange\Source;

use Iterator;
use ArrayIterator;

use Sholokhov\Exchange\Helper\Helper;

/**
 * Источник данных на основе json строки
 *
 * @package Source
 */
class Json implements Iterator
{
    use IterableTrait;

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

    /**
     * Загрузка данных
     *
     * @return Iterator
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
    private function getSourceKey(): string
    {
        return (string)($this->options['source_key'] ?? '');
    }
}