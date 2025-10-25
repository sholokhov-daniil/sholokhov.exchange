<?php

namespace Sholokhov\Exchange\Events\Exchange;

use Sholokhov\Exchange\Validators\CheckAvailableModules;
use Sholokhov\Exchange\Validators\IBlock\IBlockIdValidator;
use Sholokhov\Exchange\Target\Import\IBlock\IBlockExchangeInterface;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

/**
 * @internal
 */
class IBlockHandler
{
    /**
     * Получение валидаторов инфоблока
     *
     * @param Event $event
     * @return EventResult
     */
    public static function getValidators(Event $event): EventResult
    {
        $exchange = $event->getParameter('exchange');
        if (!($exchange instanceof IBlockExchangeInterface)) {
            return new EventResult(EventResult::UNDEFINED);
        }

        $validators = [
            new CheckAvailableModules(['iblock']),
            new IBlockIdValidator,
        ];

        return new EventResult(EventResult::SUCCESS, compact('validators'));
    }
}