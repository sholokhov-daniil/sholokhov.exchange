<?php

namespace Sholokhov\Exchange\Factory\Exchange;

use Sholokhov\Exchange\Helper\Helper;
use Sholokhov\Exchange\Validators\MapValidator;
use Sholokhov\Exchange\Validators\ValidatorInterface;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

/**
 * Создает валидатор хранилища карты обмена
 *
 * @package Facotry
 */
class MapValidatorFactory
{
    public static function create(): ValidatorInterface
    {
        return self::resolve() ?: new MapValidator;
    }

    private static function resolve(): ?ValidatorInterface
    {
        $event = new Event(Helper::getModuleID(), 'onBeforeCreateMapValidator');
        $event->send();

        foreach ($event->getResults() as $result) {
            if ($result->getType() !== EventResult::SUCCESS) {
                continue;
            }

            $parameters = (array)$result->getParameters();
            $entity = $parameters['entity'] ?? null;

            if ($entity instanceof ValidatorInterface) {
                return $entity;
            }
        }

        return null;
    }
}