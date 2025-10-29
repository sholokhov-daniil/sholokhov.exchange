<?php

namespace Sholokhov\Exchange\UI\Map;

use Sholokhov\Exchange\Helper\Helper;
use Sholokhov\Exchange\UI\Configuration\Facade\TargetRepository;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

/**
 * Производит создание провайдера данных, для UI карты обмена
 */
readonly class Factory
{
    /**
     * @param string $target Код типа обмена
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
     */
    public function create(int $entityId, string $field): ?callable
    {
        $config = TargetRepository::get($this->target);

        if (!$config) {
            return null;
        }

        $options = $config->getFieldOptions();

        return match(true) {
            isset($options[$field]) => new $options[$field]($this->target),
            isset($options['default']) => new $options['default']($this->target),
            default => $this->createExternal($entityId, $field),
        };
    }

    /**
     * Создание внешнего провайдера
     *
     * @param int $entityId
     * @param string $field
     * @return callable|null
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