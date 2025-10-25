<?php

namespace Sholokhov\Exchange;

use Sholokhov\Exchange\Fields\FieldInterface;
use Sholokhov\Exchange\Repository\Map\MappingRegistryInterface;

trait ExchangeMapTrait
{
    /**
     * Хранилище карты обмена
     *
     * @var MappingRegistryInterface
     */
    private MappingRegistryInterface $mappingRegistry;

    /**
     * Указание хранилища карты обмена
     *
     * @param MappingRegistryInterface $mappingRegistry
     * @return $this
     */
    public function setMappingRegistry(MappingRegistryInterface $mappingRegistry): static
    {
        $this->mappingRegistry = $mappingRegistry;
        return $this;
    }

    /**
     * Получение хранилища карты обмена
     *
     * @return MappingRegistryInterface|null
     */
    public function getMappingRegistry(): ?MappingRegistryInterface
    {
        return $this->mappingRegistry;
    }

    /**
     * Получение ключевого поля по которому проверяется уникальность элемента сущности
     *
     * @return FieldInterface|null
     */
    protected function getPrimaryField(): ?FieldInterface
    {
        return $this->mappingRegistry->getPrimaryField();
    }

    /**
     * Получение поля хранения кеша обмена
     *
     * @return FieldInterface|null
     */
    protected function getHashField(): ?FieldInterface
    {
        return $this->mappingRegistry->getHashField();
    }
}