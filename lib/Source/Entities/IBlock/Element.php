<?php

namespace Sholokhov\Exchange\Source\Entities\IBlock;

use Iterator;
use ArrayIterator;

use Sholokhov\Exchange\Exception\Source\SourceException;
use Sholokhov\Exchange\Source\IterableTrait;
use Sholokhov\Exchange\Provider\Entity\IBlockElementProvider;
use Sholokhov\Exchange\Provider\Entity\IBlockElementProviderInterface;

/**
 * Источник данных основан на элементах информационного блока
 *
 * @author Daniil S.
 *
 * @package Source
 */
class Element implements Iterator
{
    use IterableTrait;

    protected readonly array $options;
    protected readonly IBlockElementProviderInterface $provider;

    public function __construct(array $options, ?IBlockElementProviderInterface $provider = null)
    {
        $this->options = $options;
        $this->provider = $provider ?? new IBlockElementProvider;

        if (empty($this->options['FILTER']) || !is_array($this->options['FILTER'])) {
            throw new SourceException("Option 'FILTER' must be an array");
        }
    }

    /**
     * Загрузка элементов ИБ
     *
     * @return Iterator
     * @author Daniil S.
     */
    protected function load(): Iterator
    {
        $filter = (array)($this->options['FILTER'] ?? []);
        $order = (array)($this->options['ORDER'] ?? ['SORT' => 'ASC']);
        $select = (array)($this->options['SELECT'] ?? []);

        $result = $this->provider
            ->setFilter($filter)
            ->setOrder($order)
            ->setSelect($select)
            ->query();

        if (is_null($result)) {
            return new ArrayIterator;
        }

        $iterator = new ArrayIterator;

        while ($facade = $result->GetNextElement()) {
            $item = $facade->GetFields();
            $item['PROPERTIES'] = [];

            if (is_iterable($this->options['PROPERTIES'])) {
                foreach ($this->options['PROPERTIES'] as $code) {
                    // TODO: Оптимизировать, если будет много свойств, то тогда будет много запросов к базе данных
                    $property = $facade->GetProperty($code);
                    if (!empty($property)) {
                        $item['PROPERTIES'][$code] = $property;
                    }
                }
            }

            $iterator->append($item);
        }

        return $iterator;
    }
}