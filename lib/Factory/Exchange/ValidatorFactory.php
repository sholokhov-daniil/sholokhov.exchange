<?php

namespace Sholokhov\Exchange\Factory\Exchange;

use Sholokhov\Exchange\ExchangeInterface;
use Sholokhov\Exchange\Helper\Helper;
use Sholokhov\Exchange\Validators\ValidatorInterface;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

/**
 * Производит создание объектов валидирующих обмен данных
 * @internal
 */
class ValidatorFactory
{
    /**
     * Создание валидаторов
     *
     * @param ExchangeInterface $exchange
     * @return ValidatorInterface[]
     */
    public static function create(ExchangeInterface $exchange): array
    {
        $result = [];
        $event = new Event(Helper::getModuleID(), 'onBeforeCreateValidator', compact('exchange'));
        $event->send();

        foreach ($event->getResults() as $eventResult) {
            if ($eventResult->getType() !== EventResult::SUCCESS) {
                continue;
            }

            $parameters = (array)$eventResult->getParameters();
            $validators = (array)($parameters['validators'] ?? []);

            foreach ($validators as $entity) {
                if ($entity instanceof ValidatorInterface) {
                    $result[] = $entity;
                }
            }
        }

        return $result;
    }
}