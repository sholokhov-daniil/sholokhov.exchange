<?php

namespace Sholokhov\Exchange\Source\Entities\IBlock;

use CIBlockResult;

use Sholokhov\Exchange\Source\Entities\AbstractEntitySource;
use Sholokhov\Exchange\Provider\Entity\IBlockElementProvider;
use Sholokhov\Exchange\Provider\Entity\IBlockProviderInterface;

/**
 * Источник данных основан на элементах информационного блока (IBlock)
 *
 * Поддерживает ленивую загрузку данных по батчам с фильтрацией, выборкой полей
 * и свойств. Реализует интерфейс Iterator для удобной итерации через foreach.
 *
 * Особенности:
 * - Поддержка батчевой загрузки через ID
 * - Возможность выбирать поля и свойства элементов
 * - Минимальное потребление памяти при больших объёмах данных
 *
 * @package Source
 */
class Element extends AbstractEntitySource
{
    /**
     * Поля для выборки
     *
     * @var array
     */
    protected array $select = ['ID', 'IBLOCK_ID', 'NAME'];

    /**
     * Свойства элементов, которые нужно загрузить
     *
     * @var array
     */
    protected array $properties = [];

    /**
     * Провайдер элементов ИБ
     *
     * @var IBlockProviderInterface
     */
    protected readonly IBlockProviderInterface $provider;

    /**
     * @param IBlockProviderInterface|null $provider Провайдер элементов ИБ
     */
    public function __construct(?IBlockProviderInterface $provider = null)
    {
        $this->provider = $provider ?? new IBlockElementProvider;
    }

    /**
     * Устанавливает ID информационного блока
     *
     * @param int $id ID информационного блока
     *
     * @return $this
     */
    public function setIBlockId(int $id): static
    {
        $this->filter['=IBLOCK_ID'] = $id;
        $this->rewind();
        return $this;
    }

    /**
     * Устанавливает свойства элементов для выборки
     *
     * @param array $properties Массив кодов свойств
     *
     * @return $this
     */
    public function setProperties(array $properties): static
    {
        $this->properties = $properties;
        return $this;
    }

    /**
     * Загружает следующий батч элементов из инфоблока
     *
     * - Поддерживает фильтрацию по последнему ID
     * - Загружает поля и свойства
     * - Обновляет состояние итератора
     *
     * @return void
     */
    protected function fetchBatch(): void
    {
        if ($this->finished) {
            return;
        }

        $filter = $this->buildFilter();
        $result = $this->executeQuery($filter);

        $this->batch = [];

        if (is_null($result)) {
            $this->finished = true;
            return;
        }

        $this->processResult($result);
    }

    /**
     * Выполняет запрос к провайдеру элементов ИБ
     *
     * @param array $filter
     *
     * @return CIBlockResult|null
     */
    protected function executeQuery(array $filter): ?CIBlockResult
    {
        return $this->provider
            ->setFilter($filter)
            ->setOrder(['ID' => 'ASC'])
            ->setSelect($this->select)
            ->setLimit($this->limit)
            ->query();
    }

    /**
     * Обрабатывает результат запроса и формирует батч
     *
     * @param CIBlockResult $result
     *
     * @return void
     */
    protected function processResult(CIBlockResult $result): void
    {
        while ($element = $result->GetNextElement()) {
            $item = $element->GetFields();
            $item['PROPERTIES'] = [];

            $this->lastId = $item['ID'];

            if (!empty($this->properties)) {
                $properties = $element->GetProperties();

                foreach ($this->properties as $code) {
                    if (!empty($properties[$code])) {
                        $item['PROPERTIES'][$code] = $properties[$code];
                    }
                }
            }

            $this->batch[] = $item;
        }
    }
}