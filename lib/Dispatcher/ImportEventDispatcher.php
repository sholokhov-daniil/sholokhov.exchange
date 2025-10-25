<?php

namespace Sholokhov\Exchange\Dispatcher;

use Sholokhov\Exchange\ExchangeInterface;
use Sholokhov\Exchange\Exception\Target\ExchangeItemStoppedException;
use Sholokhov\Exchange\Helper\Helper;
use Sholokhov\Exchange\Messages\DataResultInterface;
use Sholokhov\Exchange\Messages\Type\Error;
use Sholokhov\Exchange\Messages\Type\EventResult;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult as BxEventResult;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * @internal
 */
class ImportEventDispatcher implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        protected readonly ExternalEventTypes $type,
        protected readonly ExchangeInterface $exchange
    )
    {
    }

    /**
     * Отправить сообщение подписчикам начала создания элемента
     *
     * @param array $fields Данные на основе которых создается элемент
     * @return EventResult
     */
    public function beforeAdd(array &$fields): EventResult
    {
        $parameters = [
            'fields' => &$fields,
            'exchange' => $this->exchange
        ];

        return $this->dispatchBeforeEvent(
            $this->type->beforeAdd,
            $parameters,
            'The creation of the item has been stopped'
        );
    }

    /**
     * @param array $fields Данные используемые при деактивации
     * @return void
     */
    public function beforeDeactivate(array $fields): void
    {
        $this->dispatch($this->type->beforeDeactivate, $fields);
    }

    /**
     * @param array $fields Поля принимающие участие в создании сущности
     * @param DataResultInterface $result Результат создания элемента
     * @return void
     */
    public function afterAdd(array $fields, DataResultInterface $result): void
    {
        $this->dispatch($this->type->afterAdd, compact('result', 'fields'));
    }

    /**
     * Событие перед обновлением
     *
     * @param int $id ID обновляемого элемента
     * @param array $item Обновляемые данные
     * @return EventResult
     */
    public function beforeUpdate(int $id, array &$item): EventResult
    {
        $parameters = [
            'fields' => &$item,
            'id' => $id,
            'exchange' => $this->exchange
        ];

        return $this->dispatchBeforeEvent(
            $this->type->beforeUpdate,
            $parameters,
            'The update of the item has been stopped'
        );
    }

    /**
     * Отправить сообщение подписчикам окончания обновления элемента сущности
     *
     * @param int $id ID элемента
     * @param array $fields Обновляемые данные
     * @param DataResultInterface $result Результат обновления
     * @return void
     */
    public function afterUpdate(int $id, array $fields, DataResultInterface $result): void
    {
        $this->dispatch($this->type->afterUpdate, compact('id', 'fields', 'result'));
    }

    /**
     * Отправка сообщения подписчикам перед выполнением действия
     *
     * @param string $type Тип события
     * @param array $fields Данные события
     * @param string $stoppedMessage Логируемое сообщение при остановке выполнения действия
     * @return EventResult
     */
    private function dispatchBeforeEvent(string $type, array $fields, string $stoppedMessage): EventResult
    {
        $result = new EventResult;

        if (!$type) {
            return $result;
        }

        try {
            $iterator = $this->dispatch($type, $fields);

            foreach ($iterator as $eventResult) {
                if ($eventResult->getType() === BxEventResult::SUCCESS) {
                    continue;
                }

                $parameters = $eventResult->getParameters();
                if (isset($parameters['ERRORS']) && is_array($parameters['ERRORS'])) {
                    foreach ($parameters['ERRORS'] as $error) {
                        $result->addError(new Error($error, 300));
                    }
                } else {
                    $result->addError(new Error('Unknown error', 300));
                }
            }
        } catch (ExchangeItemStoppedException $exception) {
            $this->logger?->warning($exception->getMessage() ?: $stoppedMessage);
            $result->setStopped();
        }

        return $result;
    }

    /**
     * Отправить событие слушателям
     *
     * @param string $type
     * @param array $fields
     * @return iterable
     */
    private function dispatch(string $type, array $fields): iterable
    {
        $event = new Event(Helper::getModuleID(), $type, $fields);
        $event->send();

        return $event->getResults();
    }
}