<?php

namespace Sholokhov\Exchange\Dispatcher;

class ExternalEventTypes
{
    public string $beforeDeactivate = '';
    public string $beforeUpdate = '';
    public string $afterUpdate = '';
    public string $beforeAdd = '';
    public string $afterAdd = '';

    public static function fromArray(array $events): static
    {
        $dto = new static;
        $dto->beforeDeactivate = $events['before_deactivate'] ?? '';
        $dto->beforeUpdate = $events['before_update'] ?? '';
        $dto->afterUpdate = $events['after_update'] ?? '';
        $dto->beforeAdd = $events['before_add'] ?? '';
        $dto->afterAdd = $events['after_add'] ?? '';

        return $dto;
    }
}