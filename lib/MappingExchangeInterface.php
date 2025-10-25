<?php

namespace Sholokhov\Exchange;

use Sholokhov\Exchange\Repository\Map\MappingRegistryInterface;

/**
 * Структура обмена согласно пользовательской карте
 */
interface MappingExchangeInterface extends ExchangeInterface
{
    /**
     * Указание хранилища карты обмена
     *
     * @param MappingRegistryInterface $mappingRegistry
     * @return $this
     */
    public function setMappingRegistry(MappingRegistryInterface $mappingRegistry): static;

    /**
     * Карта обмена используемая обменом
     *
     * @return MappingRegistryInterface|null
     */
    public function getMappingRegistry(): ?MappingRegistryInterface;
}