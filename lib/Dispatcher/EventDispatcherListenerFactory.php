<?php

namespace Sholokhov\Exchange\Dispatcher;

use ReflectionException;

use Sholokhov\Exchange\Helper\Helper;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

use Psr\EventDispatcher\ListenerProviderInterface;

class EventDispatcherListenerFactory
{
    /**
     * Создание процесса обмена
     *
     * @param object $exchange
     * @return ListenerProviderInterface
     * @throws ReflectionException
     */
    public static function create(object $exchange): ListenerProviderInterface
    {
        return self::resolve($exchange) ?: new EntityEventProvider($exchange);
    }

    /**
     * Получение пользовательского процесса через событие
     *
     * @param object $exchange
     * @return ListenerProviderInterface|null
     */
    private static function resolve(object $exchange): ?ListenerProviderInterface
    {
        $event = new Event(Helper::getModuleID(), 'onBeforeCreateEventListenerProvider', compact('exchange'));
        $event->send();

        foreach ($event->getResults() as $result) {
            if ($result->getType() !== EventResult::SUCCESS) {
                continue;
            }

            $parameters = (array)$result->getParameters();
            $entity = $parameters['entity'] ?? null;

            if ($entity instanceof ListenerProviderInterface) {
                return $entity;
            }
        }

        return null;
    }
}