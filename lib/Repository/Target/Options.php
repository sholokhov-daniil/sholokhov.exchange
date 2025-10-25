<?php

namespace Sholokhov\Exchange\Repository\Target;

use Sholokhov\Exchange\Repository\Types\Memory;
use Sholokhov\Exchange\Fields\FieldInterface;

/**
 * @package Repository
 */
class Options extends Memory
{
    /**
     * Карта обмена
     *
     * @return FieldInterface[]
     */
    public function getMap(): array
    {
        return (array)$this->get('map');
    }

    /**
     * Внешний ключ.
     * Используется для идентификации элементов во время импорта.
     *
     * @return string
     */
    public function getForeignKey(): string
    {
        return (string)$this->get('foreign_key');
    }
}