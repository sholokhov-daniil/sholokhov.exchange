<?php

namespace Sholokhov\Exchange;

use Sholokhov\Exchange\Fields\FieldInterface;
use Sholokhov\Exchange\Messages\ExchangeResultInterface;

use Psr\Log\LoggerAwareInterface;

interface ExchangeInterface extends LoggerAwareInterface
{
    /**
     * Запуск обмена данными
     *
     * @param iterable $source
     * @return ExchangeResultInterface
     */
    public function execute(iterable $source): ExchangeResultInterface;

    /**
     * Свойство является множественным
     *
     * @param FieldInterface $field
     * @return bool
     */
    public function isMultipleField(FieldInterface $field): bool;
}