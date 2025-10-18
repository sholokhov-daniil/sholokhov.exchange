<?php

namespace Sholokhov\Exchange\Preparation;

use Sholokhov\Exchange\Fields\FieldInterface;
use Sholokhov\Exchange\Helper\FieldHelper;
use Sholokhov\Exchange\Normalizers\ValueNormalizer;

/**
 * Базовый класс преобразования значения
 *
 * @internal
 * @package Preparation
 */
abstract class AbstractFieldPreparationPipeline implements FieldPreparationPipelineInterface
{
    protected readonly Chain $engine;
    protected readonly ?ValueNormalizer $normalizer;

    public function __construct(ValueNormalizer $normalizer = null)
    {
        $this->normalizer = $normalizer;
        $this->engine = new Chain;
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

    /**
     * Логика преобразования значения
     *
     * @param FieldInterface $field
     * @param array $item
     * @return mixed
     */
    protected function logic(FieldInterface $field, array $item): mixed
    {
        $value = FieldHelper::getValue($item, $field);

        if ($this->normalizer) {
            $value = $this->normalizer->normalize($value, $field);
        }

        if (is_callable($field->getPreparation())) {
            $value = call_user_func($field->getPreparation(), $value, $field);
        } else {
            $value = $this->engine->prepare($value, $field);
        }

        return $value;
    }
}