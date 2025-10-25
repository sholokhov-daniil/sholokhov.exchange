<?php

namespace Sholokhov\Exchange\Events\Exchange\Import\Sale;

use Sholokhov\Exchange\Target\Import\Sale\Warehouse;
use Sholokhov\Exchange\Validators\CheckAvailableModules;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

/**
 * @internal
 */
class WarehouseHandler
{
    /**
     * Получение валидаторов импорта складов
     *
     * @param Event $event
     * @return EventResult
     */
    public static function getValidators(Event $event): EventResult
    {
        $exchange = $event->getParameter('exchange');
        if (!($exchange instanceof Warehouse)) {
            return new EventResult(EventResult::UNDEFINED);
        }

        $validators = [
            new CheckAvailableModules(['catalog'])
        ];

        return new EventResult(EventResult::SUCCESS, compact('validators'));
    }
}