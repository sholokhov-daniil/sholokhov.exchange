<?php

namespace Sholokhov\Exchange;

use Exception;

use Sholokhov\Exchange\Dispatcher\ExternalEventTypes;
use Sholokhov\Exchange\Factory\Exchange\ProcessorFactory;
use Sholokhov\Exchange\Messages\ExchangeResultInterface;
use Sholokhov\Exchange\Messages\Type\DataResult;
use Sholokhov\Exchange\Messages\DataResultInterface;
use Sholokhov\Exchange\Dispatcher\ImportEventDispatcher;
use Sholokhov\Exchange\Preparation\PreparationInterface;
use Sholokhov\Exchange\Processor\ProcessorInterface;

use Bitrix\Main\EventManager;

/**
 * Базовый процесс импорта данных
 *
 * @package Import
 */
abstract class AbstractImport extends AbstractExchange implements ImportInterface
{
    /**
     * Шина внешних событий импорта.
     * Шина работает на основе событий bitrix {@see EventManager}
     *
     * @var ImportEventDispatcher
     */
    protected readonly ImportEventDispatcher $events;

    /**
     * Логика выполнения импорта данных
     *
     * @var ProcessorInterface
     */
    protected readonly ProcessorInterface $processor;

    /**
     * Логика добавления элемента сущности
     *
     * @param array $fields
     * @param array $originalFields
     * @return DataResultInterface
     */
    abstract protected function doAdd(array $fields, array $originalFields): DataResultInterface;

    /**
     * Логика обновления значения
     *
     * @param int $id
     * @param array $fields Обновляемый набор параметров
     * @param array $originalFields
     * @return DataResultInterface
     */
    abstract protected function doUpdate(int $id, array $fields, array $originalFields): DataResultInterface;

    /**
     * Логика проверки наличия элемента сущности
     *
     * @param array $item
     * @return bool
     */
    abstract protected function doExist(array $item): bool;

    /**
     * Преобразование данных, для добавления элемента сущности
     *
     * @param array $item
     * @return array
     */
    abstract protected function prepareForAdd(array $item): array;

    /**
     * Преобразование данных для обновления
     *
     * @param array $item
     * @return array
     */
    abstract protected function prepareForUpdate(array $item): array;

    /**
     * Получение ID элемента сущности
     *
     * @param array $item
     * @return int
     */
    abstract protected function resolveId(array $item): int;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->configuration();
    }

    /**
     * Деактивация элемента сущности по итогам обмена.
     *
     * @final
     * @return void
     */
    final public function deactivate(): void
    {
        if ($this->canDeactivate()) {
            $this->doDeactivate();
        }
    }

    /**
     * Производит деактивацию элементов по итогам импорта
     * Предназначен, для переопределения наследниками
     *
     * @return void
     */
    protected function doDeactivate(): void
    {
    }

    /**
     * Логика определения необходимости запуска деактивации. По умолчанию деактивация отключена
     * Предназначен, для переопределения наследниками
     *
     * @return bool
     */
    protected function canDeactivate(): bool
    {
        return false;
    }

    /**
     * Получение доступных внешних событий обмена.
     *
     * Если обмен не поддерживает возможность вмешательства в его работы из вне,
     * то необходимо вернуть пустой объект.
     *
     * Метод предназначен, для переопределения наследниками
     *
     * @return ExternalEventTypes
     */
    protected function getEventTypes(): ExternalEventTypes
    {
        return new ExternalEventTypes;
    }

    /**
     * Конфигурация обмена
     *
     * @return void
     * @throws Exception
     */
    protected function configuration(): void
    {
        $this->processor = ProcessorFactory::create($this);
        $this->events = new ImportEventDispatcher($this->getEventTypes(), $this);
    }

    /**
     * Проверяет наличие элемента
     *
     * @final
     * @param array $item
     * @return bool
     */
    final public function exists(array $item): bool
    {
        return $this->resolveId($item) ?: $this->doExist($item);
    }

    /**
     * Обновление элемента сущности
     *
     * @final
     * @param array $item
     * @return DataResultInterface
     */
    final public function update(array $item): DataResultInterface
    {
        $id = $this->resolveId($item);
        $fields = $this->prepareForUpdate($item);

        $beforeUpdate = $this->events->beforeUpdate($id, $fields);
        if (!$beforeUpdate->isSuccess()) {
            return (new DataResult)->addErrors($beforeUpdate->getErrors());
        }

        if ($beforeUpdate->isStopped()) {
            return new DataResult;
        }

        $result = $this->doUpdate($id, $fields, $item);
        $result->setData($id);
        $this->events->afterUpdate($id, $fields, $result);

        return $result;
    }

    /**
     * Добавление элемента в сущность
     *
     * @final
     * @param array $item
     * @return DataResultInterface
     */
    final public function add(array $item): DataResultInterface
    {
        $fields = $this->prepareForAdd($item);

        $beforeAdd = $this->events->beforeAdd($fields);
        if (!$beforeAdd->isSuccess()) {
            return (new DataResult)->addErrors($beforeAdd->getErrors());
        }

        if ($beforeAdd->isStopped()) {
            return new DataResult;
        }

        $result = $this->doAdd($fields, $item);
        $this->events->afterAdd($fields, $result);

        return $result;
    }

    /**
     * Указание преобразователя данных
     *
     * @param PreparationInterface $preparation
     * @return $this
     */
    public function addPrepared(PreparationInterface $preparation): static
    {
        $this->processor->addPrepared($preparation);
        return $this;
    }

    /**
     * Логика импорта данных
     *
     * @param iterable $source
     * @param ExchangeResultInterface $result
     * @return void
     */
    protected function logic(iterable $source, ExchangeResultInterface $result): void
    {
        if ($this->logger) {
            $this->processor->setLogger($this->logger);
            $this->events->setLogger($this->logger);
        }

        $this->processor->run($source, $result);
    }
}