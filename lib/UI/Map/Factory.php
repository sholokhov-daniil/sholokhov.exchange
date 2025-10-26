<?php

namespace Sholokhov\Exchange\UI\Map;

use Sholokhov\Exchange\Helper\Helper;
use Sholokhov\Exchange\Helper\Config;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

/**
 * Производит создание провайдера данных, для UI карты обмена
 *
 * @since 1.2.0
 * @version 1.2.0
 */
readonly class Factory
{
    /**
     * @param string $target Код типа обмена
     * @since 1.2.0
     * @version 1.2.0
     */
    public function __construct(private readonly string $target)
    {
    }

    /**
     * Создание провайдера данных
     *
     * @param int $entityId
     * @param string $field
     * @return callable|null
     * @since 1.2.0
     * @version 1.2.0
     */
    public function create(int $entityId, string $field): ?callable
    {
        $iterator = Config::get("map", []);

        return match(true) {
            isset($iterator[$this->target][$field]) => new $iterator[$this->target][$field]($this->target),
            isset($iterator[$this->target]['default']) => new $iterator[$this->target]['default']($this->target),
            default => $this->createExternal($entityId, $field),
        };
    }

    /**
     * Создание внешнего провайдера
     *
     * @param int $entityId
     * @param string $field
     * @return callable|null
     * @since 1.2.0
     * @version 1.2.0
     */
    private function createExternal(int $entityId, string $field): ?callable
    {
        $parameters = [
            'target' => $this->target,
            'field' => $field,
            'entityId' => $entityId,
        ];

        $event = new Event(Helper::getModuleID(), 'initUIMapProvider', $parameters);
        $event->send();

        foreach ($event->getResults() as $result) {
            if (
                $result->getType() === EventResult::SUCCESS
                && ($provider = $result->getParameters()['provider'])
                && is_callable($provider)
            ) {
                return $provider;
            }
        }

        return null;
    }
}