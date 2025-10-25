<?php

namespace Sholokhov\Exchange\Events\Exchange\Import;

use Sholokhov\Exchange\Target\Import\Highloadblock\Element;

use Sholokhov\Exchange\Validators\CheckAvailableModules;
use Sholokhov\Exchange\Validators\Handbook\EntityIdValidator;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

/**
 * @internal
 */
class HandbookHandler
{
    /**
     * Инициализация валидаторов
     *
     * @param Event $event
     * @return EventResult
     */
    public static function getValidators(Event $event): EventResult
    {
        $exchange = $event->getParameter('exchange');
        if (!($exchange instanceof Element)) {
            return new EventResult(EventResult::UNDEFINED);
        }

        $validators = [
            new CheckAvailableModules(['highloadblock']),
            new EntityIdValidator,
        ];

        return new EventResult(EventResult::SUCCESS, compact('validators'));
    }
}