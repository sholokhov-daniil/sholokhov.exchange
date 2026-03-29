<?php

namespace Sholokhov\Exchange\Source;

use Iterator;

use Sholokhov\Exchange\Reader\DataReaderInterface;
use Sholokhov\Exchange\Exception\Reader\ReaderException;

/**
 * Базовое представление xml источников данных
 *
 * @internal
 *
 * @package Source
 */
abstract class AbstractXml implements Iterator
{
    use IterableTrait;

    /**
     * Родительский тег элементов
     *
     * @var string
     */
    protected string $rootTag = 'data';

    /**
     * Провайдер данных
     *
     * @var DataReaderInterface
     */
    protected readonly DataReaderInterface $reader;

    /**
     * @param DataReaderInterface $reader Провайдер данных
     */
    public function __construct(DataReaderInterface $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Парсинг xml файла
     *
     * @param mixed $resource
     * @return Iterator
     */
    abstract protected function parsing(mixed $resource): Iterator;

    /**
     * Указание родительского тега элементов
     *
     * Если изменение происходит после формирования указателя({@see self::fetch()}), то он сбрасывается
     *
     * @param string $rootTag
     * @return $this
     */
    public function setRootTag(string $rootTag): self
    {
        $this->rootTag = $rootTag;

        if ($this->iterator) {
            $this->iterator = null;
        }

        return $this;
    }

    /**
     * Загрузка данных источника
     *
     * @return Iterator
     * @throws ReaderException
     */
    final protected function load(): Iterator
    {
        $resource = $this->reader->read();
        $iterator = $this->parsing($resource);
        fclose($resource);


        return $iterator;
    }
}