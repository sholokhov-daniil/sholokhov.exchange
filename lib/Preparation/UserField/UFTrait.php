<?php

namespace Sholokhov\Exchange\Preparation\UserField;

use Sholokhov\Exchange\Repository\Fields\UFRepository;

/**
 * @package Preparation
 */
trait UFTrait
{
    /**
     * @var string
     */
    protected readonly string $entityId;

    /**
     * Хранилище информации о свойствах
     *
     * @var UFRepository
     */
    private UFRepository $repository;

    /**
     * Получения хранилища информации свойств
     *
     * @return UFRepository
     */
    final protected function getFieldRepository(): UFRepository
    {
        return $this->repository ??= new UFRepository(['entity_id' => $this->entityId]);
    }
}