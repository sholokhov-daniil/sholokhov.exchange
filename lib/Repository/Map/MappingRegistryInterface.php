<?php

namespace Sholokhov\Exchange\Repository\Map;

use Sholokhov\Exchange\Fields\FieldInterface;

/**
 * Хранилище правил соответствия полей обмена
 *
 * @package Repository
 */
interface MappingRegistryInterface
{
    /**
     * Указание карты обмена
     *
     * @param FieldInterface[] $map
     * @return $this
     */
    public function setFields(array $map): static;

    /**
     * Получение карты обмена
     *
     * @return FieldInterface[]
     */
    public function getFields(): array;

    /**
     * Получение свойства отвечающего за идентификацию значения
     *
     * @return FieldInterface|null
     */
    public function getPrimaryField(): ?FieldInterface;

    /**
     * Получение поля отвечающего за хеш обмена
     *
     * @return FieldInterface|null
     */
    public function getHashField(): ?FieldInterface;
}