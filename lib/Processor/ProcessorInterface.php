<?php

namespace Sholokhov\Exchange\Processor;

use Sholokhov\Exchange\Messages\ExchangeResultInterface;
use Sholokhov\Exchange\Preparation\PreparationInterface;

/**
 * @package Processor
 */
interface ProcessorInterface
{
    /**
     * Запуск процесса обработки элемента
     *
     * @param iterable<array> $source
     * @param ExchangeResultInterface $result
     * @return void
     */
    public function run(iterable $source, ExchangeResultInterface $result): void;

    /**
     * Добавление преобразователя данных
     *
     * @param PreparationInterface $preparation
     * @return $this
     */
    public function addPrepared(PreparationInterface $preparation): static;
}