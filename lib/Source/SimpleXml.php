<?php

namespace Sholokhov\Exchange\Source;

use Iterator;
use ArrayIterator;

use Sholokhov\Exchange\Helper\Helper;

use Symfony\Component\Serializer\Encoder\XmlEncoder;

/**
 * Упрощенный источник данных xml.
 * Весь файл хранится в памяти машины, что обеспечивает быстродействие,
 * но требователен к допустимому объему ОЗУ
 *
 * Рекомендуется для использования, если объем данных не большой
 *
 * @package Source
 */
class SimpleXml extends AbstractXml
{
    /**
     * Чтение и парсинг xml файла
     *
     * @param resource $resource
     * @return Iterator
     */
    protected function parsing(mixed $resource): Iterator
    {
        if (!$resource) {
            return new ArrayIterator;
        }

        $content = stream_get_contents($resource);
        if (trim($content) === '') {
            return new ArrayIterator;
        }

        $encoder = new XmlEncoder;
        $data = $encoder->decode($content, XmlEncoder::FORMAT);

        if (!is_array($data)) {
            return new ArrayIterator;
        }

        $data = Helper::getArrValueByPath($data, $this->rootTag);

        if (!$data) {
            return new ArrayIterator;
        }

        return array_is_list($data) ? new ArrayIterator($data) : new ArrayIterator([$data]);
    }
}