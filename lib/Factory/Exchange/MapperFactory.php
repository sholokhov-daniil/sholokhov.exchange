<?php

namespace Sholokhov\Exchange\Factory\Exchange;

use Sholokhov\Exchange\Helper\Helper;
use Sholokhov\Exchange\Repository\Map\MappingRegistry;
use Sholokhov\Exchange\Repository\Map\MappingRegistryInterface;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

/**
 * Создает хранилище карты обмена
 *
 * @package Facotry
 */
class MapperFactory
{
    /**
     * Создание карты обмена
     *
     * @return MappingRegistryInterface
     */
    public static function create(): MappingRegistryInterface
    {
        return self::resolve() ?: new MappingRegistry(MapValidatorFactory::create());
    }

    /**
     * Получение карты обмена из импорта
     *
     * @return MappingRegistryInterface|null
     */
    private static function resolve(): ?MappingRegistryInterface
    {
        $event = new Event(Helper::getModuleID(), 'onBeforeCreateMapper');
        $event->send();

        foreach ($event->getResults() as $result) {
            if ($result->getType() !== EventResult::SUCCESS) {
                continue;
            }

            $parameters = (array)$result->getParameters();
            $entity = $parameters['entity'] ?? null;

            if ($entity instanceof MappingRegistryInterface) {
                return $entity;
            }
        }

        return null;
    }
}