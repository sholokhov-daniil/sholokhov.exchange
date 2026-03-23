<?php

namespace Sholokhov\Exchange\Source\Entities\IBlock;

use CIBlockResult;

use Sholokhov\Exchange\Provider\Entity\IBlockSectionProvider;
use Sholokhov\Exchange\Source\Entities\AbstractEntitySource;
use Sholokhov\Exchange\Provider\Entity\IBlockProviderInterface;

use Bitrix\Main\Loader;

/**
 * Источник данных основан на разделах информационного блока (IBlock)
 *
 * Поддерживает ленивую загрузку данных по батчам с фильтрацией, выборкой полей и свойств.
 * Реализует интерфейс Iterator для удобной итерации через foreach.
 *
 * @package Source
 */
class Section extends AbstractEntitySource
{
    /**
     * Поля для выборки
     *
     * @var array
     */
    protected array $select = ['ID', 'IBLOCK_ID', 'NAME'];

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
        Loader::includeModule('iblock');
        $this->provider = $provider ?? new IBlockSectionProvider;
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

        $this->batch = $this->processResult($result);
    }

    /**
     * Обрабатывает результат запроса и формирует батч
     *
     * @param CIBlockResult $result
     *
     * @return array
     */
    protected function processResult(CIBlockResult $result): array
    {
        $batch = [];

        while ($item = $result->Fetch()) {
            $this->lastId = $item['ID'];
            $batch[] = $item;
        }

        return $batch;
    }
}