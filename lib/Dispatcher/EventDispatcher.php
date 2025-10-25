<?php

namespace Sholokhov\Exchange\Dispatcher;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

class EventDispatcher implements EventDispatcherInterface
{
    public function __construct(private readonly ListenerProviderInterface $provider)
    {
    }

    /**
     * Вызвать событие
     *
     * @param object $event
     * @return void
     */
    public function dispatch(object $event): void
    {
        $iterator = $this->provider->getListenersForEvent($event);

        foreach ($iterator as $listener) {
            $listener($event);
        }
    }
}