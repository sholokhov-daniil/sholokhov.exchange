<?php

namespace Sholokhov\Exchange\Dispatcher;

use Sholokhov\Exchange\Events\Event;
use Sholokhov\Exchange\Events\EventInterface;
use Sholokhov\Exchange\Events\ExchangeEvent;
use Sholokhov\Exchange\Messages\DataResultInterface;

use Psr\EventDispatcher\EventDispatcherInterface;

class InternalEventDispatcher
{
    public function __construct(private readonly EventDispatcherInterface $dispatcher)
    {
    }

    /**
     * Событие перед обменом элемента сущности
     *
     * @param array $item
     * @return array
     * @throws \Throwable
     */
    public function beforeImportItem(array $item): array
    {
        $event = new Event(ExchangeEvent::BeforeImportItem->value, ['item' => &$item]);
        $this->dispatch($event);

        return $event->getParameters()['item'];
    }

    public function beforeUpdate(array $item): array
    {
        $event = new Event(ExchangeEvent::BeforeUpdate->value, ['item' => &$item]);
        $this->dispatch($event);

        return $event->getParameters()['item'];
    }

    public function afterUpdate(array $item, DataResultInterface $result): void
    {
        $event = new Event(ExchangeEvent::AfterUpdate->value, ['item' => $item, 'result' => $result]);
        $this->dispatch($event);
    }

    public function beforeAdd(array $item): array
    {
        $event = new Event(ExchangeEvent::BeforeAdd->value, ['item' => &$item]);
        $this->dispatch($event);

        return $event->getParameters()['item'];
    }

    public function afterAdd(array $item, DataResultInterface $result): void
    {
        $event = new Event(ExchangeEvent::AfterAdd->value, ['item' => $item, 'result' => $result]);
        $this->dispatch($event);
    }

    public function afterImport(array $item, DataResultInterface $result): void
    {
        $event = new Event(ExchangeEvent::AfterImportItem->value, ['item' => $item, 'result' => $result]);
        $this->dispatch($event);
    }

    private function dispatch(EventInterface $event)
    {
        try {
            $this->dispatcher->dispatch($event);
        } catch (\Throwable $throwable) {
            // централизованно логируем/оборачиваем/решаем, что делать
            // например: логгируем и помечаем результат ошибкой
            // или пробрасываем дальше в зависимости от конфигурации
            throw $throwable;
        }
    }
}