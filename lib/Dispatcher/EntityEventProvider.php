<?php

namespace Sholokhov\Exchange\Dispatcher;

use ReflectionException;
use InvalidArgumentException;

use Sholokhov\Exchange\Events\EventInterface;

use Psr\EventDispatcher\ListenerProviderInterface;

/**
 * Провайдер события на основе объекта
 */
class EntityEventProvider implements ListenerProviderInterface
{
    private array $listeners = [];

    /**
     * @param object $entity Объект, для которого предназначаются события
     * @throws ReflectionException
     */
    public function __construct(object $entity)
    {
        $this->load($entity);
    }

    /**
     * Получение слушателей события
     *
     * @param object $event
     * @return iterable
     */
    public function getListenersForEvent(object $event): iterable
    {
        if (!($event instanceof EventInterface)) {
            throw new InvalidArgumentException('event must be an instance of ' . EventInterface::class);
        }

        $type = $event->getType();

        if (!empty($this->listeners[$type])) {
            yield from $this->listeners[$type] ?? [];
        }
    }

    /**
     * Подписаться на событие
     *
     * @param string $type
     * @param callable(object):void $listener
     * @return $this
     */
    public function subscribe(string $type, callable $listener): static
    {
        $this->listeners[$type][] = $listener;
        return $this;
    }

    /**
     * Инициализация событий на основе объекта
     * 
     * @param object $event
     * @return void
     * @throws ReflectionException
     */
    private function load(object $event): void
    {
        (new AttributeListenerLoader($this))->load($event);
    }
}