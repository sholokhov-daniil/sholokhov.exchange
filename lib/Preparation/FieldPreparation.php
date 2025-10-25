<?php

namespace Sholokhov\Exchange\Preparation;

use Sholokhov\Exchange\Fields\FieldInterface;
use Sholokhov\Exchange\Helper\FieldHelper;
use Sholokhov\Exchange\Messages\DataResultInterface;
use Sholokhov\Exchange\Normalizers\ValueNormalizer;

/**
 * Преобразует обработку значения на основе свойства карты обмена
 *
 * @package Preparation
 */
class FieldPreparation
{
    private readonly Chain $engine;
    private readonly ValueNormalizer $normalizer;

    public function __construct(ValueNormalizer $normalizer)
    {
        $this->normalizer = $normalizer;
        $this->engine = new Chain;
    }

    /**
     * Преобразование значение
     *
     * @param FieldInterface $field
     * @param array $item
     * @return DataResultInterface
     */
    public function prepare(FieldInterface $field, array $item): mixed
    {
        $value = FieldHelper::getValue($item, $field);
        $value = $this->normalizer->normalize($value, $field);

        if (is_callable($field->getPreparation())) {
            $value = ($field->getPreparation())($value, $field);
        } else {
            $value = $this->engine->prepare($value, $field);
        }

        return $value;
    }

    /**
     * Добавление преобразователя данных
     *
     * @param PreparationInterface $preparation
     * @return $this
     */
    public function add(PreparationInterface $preparation): static
    {
        $this->engine->add($preparation);
        return $this;
    }
}