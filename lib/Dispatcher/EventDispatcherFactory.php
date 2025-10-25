<?php


namespace Sholokhov\Exchange\Dispatcher;

use ReflectionException;

use Sholokhov\Exchange\Helper\Helper;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @inernal
 */
class EventDispatcherFactory
{
    /**
     * Создание процесса обмена
     *
     * @param object $exchange
     * @return EventDispatcherInterface
     * @throws ReflectionException
     */
    public static function create(object $exchange): EventDispatcherInterface
    {
        return self::resolve($exchange) ?: new EventDispatcher(EventDispatcherListenerFactory::create($exchange));
    }

    /**
     * Получение пользовательского процесса через событие
     *
     * @param object $exchange
     * @return EventDispatcherInterface|null
     */
    private static function resolve(object $exchange): ?EventDispatcherInterface
    {
        $event = new Event(Helper::getModuleID(), 'onBeforeCreateEventDispatcher', compact('exchange'));
        $event->send();

        foreach ($event->getResults() as $result) {
            if ($result->getType() !== EventResult::SUCCESS) {
                continue;
            }

            $parameters = (array)$result->getParameters();
            $dispatcher = $parameters['entity'] ?? null;

            if ($dispatcher instanceof EventDispatcherInterface) {
                return $dispatcher;
            }
        }

        return null;
    }
}