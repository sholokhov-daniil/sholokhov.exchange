<?php

namespace Sholokhov\Exchange\Dispatcher;

use ReflectionException;
use Sholokhov\Exchange\Events\EventInterface;
use Bitrix\Main\NotImplementedException;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Расширение описывает интерфейс {@see EventDispatchableInterface}
 */
trait EventDispatchableTrait
{
    /**
     * Диспетчер событий
     *
     * @var EventDispatcherInterface
     */
    private EventDispatcherInterface $eventDispatcher;

    /**
     * Отправить событие
     *
     * @param EventInterface|object $event
     * @return void
     * @throws NotImplementedException
     * @throws ReflectionException
     */
    public function dispatch(object $event): void
    {
        if (!($event instanceof EventInterface)) {
            throw new NotImplementedException('Event not implement ' . EventInterface::class);
        }

        $this->eventDispatcher ??= EventDispatcherFactory::create($this);
        $this->eventDispatcher->dispatch($event);
    }
}