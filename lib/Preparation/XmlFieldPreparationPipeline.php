<?php

namespace Sholokhov\Exchange\Preparation;

use Sholokhov\Exchange\Fields\Export\XmlFieldInterface;
use Sholokhov\Exchange\Fields\FieldInterface;

/**
 * Преобразует обработку значения на основе свойства карты обмена, для экспорта в XML
 *
 * @internal
 * @package Preparation
 */
class XmlFieldPreparationPipeline extends AbstractFieldPreparationPipeline
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
            $result[$field->getTo()] = [
                'value' => $this->logic($field, $item),
                'attributes' => $this->getAttributes($field, $item),
                'children_tag' => $field instanceof XmlFieldInterface ? $field->getChildrenTag() : '',
            ];
        }

        return $result;
    }

    /**
     * Получение атрибутов тега
     *
     * @param FieldInterface $field
     * @param array $item
     * @return array
     */
    protected function getAttributes(FieldInterface $field, array $item): array
    {
        $attributes = [];

        if ($field instanceof XmlFieldInterface) {
            foreach ($field->getAttributes() as $attribute) {
                $attributes[$attribute->getTo()] = $this->logic($attribute, $item);
            }
        }

        return $attributes;
    }
}