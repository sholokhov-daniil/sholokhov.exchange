<?php

namespace Sholokhov\Exchange\Source;

use ArrayIterator;
use Iterator;
use EmptyIterator;

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
     * @param mixed $resource
     * @return Iterator
     */
    protected function parsing(mixed $resource): Iterator
    {
        if (!$resource) {
            return new EmptyIterator();
        }

        $encoder = new XmlEncoder();
        $data = $encoder->decode(stream_get_contents($resource), XmlEncoder::FORMAT);
        $data = Helper::getArrValueByPath($data, $this->rootTag);

        if (!$data) {
            return new EmptyIterator();
        }

        return array_is_list($data) ? new ArrayIterator($data) : new ArrayIterator([$data]);
    }
}