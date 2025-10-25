<?php

namespace Sholokhov\Exchange\Preparation;

use Sholokhov\Exchange\Fields\FieldInterface;

/**
 * Преобразует обработку значения на основе свойства карты обмена
 *
 * @internal
 * @package Preparation
 */
class FieldPreparationPipeline extends AbstractFieldPreparationPipeline
{
    /**
     * Преобразование значение
     *
     * @param array $item
     * @param FieldInterface[] $map
     * @return array
     */
    public function prepare(array $item, array $map): array
    {
        $result = [];

        foreach ($map as $field) {
            $value = $this->logic($field, $item);

            $result[$field->getTo()] = $value;
        }

        return $result;
    }
}