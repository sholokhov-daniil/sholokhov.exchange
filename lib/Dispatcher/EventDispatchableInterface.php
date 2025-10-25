<?php

namespace Sholokhov\Exchange\Dispatcher;

use Sholokhov\Exchange\Events\EventInterface;

use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @method dispatch(EventInterface $event)
 */
interface EventDispatchableInterface extends EventDispatcherInterface
{
}