<?php

namespace Sholokhov\Exchange\Preparation;

use Sholokhov\Exchange\Fields\FieldInterface;

/**
 * @package Preparation
 */
interface FieldPreparationPipelineInterface
{
    /**
     * Преобразование значений
     *
     * @param array $item
     * @param FieldInterface[] $map
     * @return array
     */
    public function prepare(array $item, array $map): array;

    /**
     * Добавление преобразователя данных
     *
     * @param PreparationInterface $preparation
     * @return $this
     */
    public function add(PreparationInterface $preparation): static;
}