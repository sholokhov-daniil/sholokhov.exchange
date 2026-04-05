<?php

namespace Sholokhov\Exchange\Source\Entities;

use Iterator;
use Sholokhov\Exchange\Exception\Source\SourceException;
use Sholokhov\Exchange\Source\Concerns\BatchIteratorTrait;
use Sholokhov\Exchange\Provider\Entity\EntityProviderInterface;

use Bitrix\Main\DB\Result;

class EntitySource implements Iterator
{
    use BatchIteratorTrait;

    /**
     * Последний обработанный ID (для батчевой загрузки)
     *
     * @var int
     */
    protected int $lastId = 0;

    /**
     * @param EntityProviderInterface $provider Провайдер сущности
     */
    public function __construct(protected readonly EntityProviderInterface $provider)
    {
    }

    /**
     * Загружает следующий батч элементов сущности
     *
     * @return void
     * @throws SourceException
     */
    protected function fetchBatch(): void
    {
        if ($this->finished) {
            return;
        }

        $result = $this->executeQuery();

        $this->batch = [];

        if (is_null($result)) {
            $this->finished = true;
            return;
        }

        $this->processResult($result);

        if (empty($this->batch)) {
            $this->finished = true;
        }
    }

    /**
     * Обрабатывает результат запроса и формирует батч
     *
     * Обновляет последний обработанный ID для курсорной пагинации.
     *
     * @param Result $result
     * @return void
     * @throws SourceException
     */
    protected function processResult(Result $result): void
    {
        while ($item = $result->fetch()) {
            if (!$item['ID']) {
                throw new SourceException('Entity source cannot be processed');
            }

            $this->lastId = $item['ID'];
            $this->batch[] = $item;
        }
    }

    /**
     * Выполнение запроса на получение данных
     *
     * @return Result|null
     */
    protected function executeQuery(): ?Result
    {
        $filter = $this->buildFilter();

        return $this->provider
            ->setOrder(['ID' => 'ASC'])
            ->setFilter($filter)
            ->query();
    }

    /**
     * Строит фильтр для запроса к элементам ИБ
     *
     * Добавляет условие по последнему ID для батчевой загрузки.
     *
     * @return array
     */
    protected function buildFilter(): array
    {
        return array_merge(
            $this->provider->getFilter(),
            ['>ID' => $this->lastId]
        );
    }

    /**
     * Отработка сброса итератора
     *
     * @return void
     */
    protected function resetState(): void
    {
        $this->lastId = 0;
    }
}