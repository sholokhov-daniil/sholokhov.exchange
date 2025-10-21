<?php

namespace Sholokhov\Exchange\Events\Exchange\Import\IBlock;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Sholokhov\Exchange\Target\Import\IBlock\Property\PropertyEnumeration;
use Sholokhov\Exchange\Validators\IBlock\Property\EnumerationPropertyCodeValidator;
use Sholokhov\Exchange\Validators\IBlock\Property\EnumerationPropertyValidator;

/**
 * Обработчики импорта значений свойства типа список
 *
 * @internal
 */
class EnumerationHandler
{
    /**
     * Получение валидаторов свойства типа список
     *
     * @param Event $event
     * @return EventResult
     */
    public static function getValidators(Event $event): EventResult
    {
        $exchange = $event->getParameter('exchange');
        if (!($exchange instanceof PropertyEnumeration)) {
            return new EventResult(EventResult::UNDEFINED);
        }

        $validators = [
            new EnumerationPropertyCodeValidator,
            new EnumerationPropertyValidator,
        ];

        return new EventResult(EventResult::SUCCESS, compact('validators'));
    }
}