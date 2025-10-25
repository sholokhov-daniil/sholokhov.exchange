<?php

namespace Sholokhov\Exchange\Fields\Export;

use Sholokhov\Exchange\Fields\FieldInterface;

interface XmlFieldInterface extends FieldInterface
{
    /**
     * Получение доступных атрибутов
     *
     * @return FieldInterface[]
     */
    public function getAttributes(): array;

    /**
     * Значение является множественным
     *
     * Если присутствует значение, то считается, что хранит множественное значение
     *
     * @return string
     */
    public function getChildrenTag(): string;
}