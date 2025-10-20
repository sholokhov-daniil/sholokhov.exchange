<?php

namespace Sholokhov\Exchange\Events\Exchange\Import;

use Sholokhov\Exchange\Validators\Handbook\EntityIdValidator;
use Sholokhov\Exchange\Validators\UserFields\PropertyCodeValidate;
use Sholokhov\Exchange\Target\Import\UserFields\ExchangeUserFieldInterface;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

/**
 * @internal
 */
class UFPropertyHandler
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
        if (!($exchange instanceof ExchangeUserFieldInterface)) {
            return new EventResult(EventResult::UNDEFINED);
        }

        $validators = [
            new EntityIdValidator,
            new PropertyCodeValidate,
        ];

        return new EventResult(EventResult::SUCCESS, compact('validators'));
    }
}