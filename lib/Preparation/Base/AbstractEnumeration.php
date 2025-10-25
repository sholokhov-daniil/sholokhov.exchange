<?php

namespace Sholokhov\Exchange\Preparation\Base;

use Exception;

use Sholokhov\Exchange\Fields\Field;
use Sholokhov\Exchange\ExchangeInterface;
use Sholokhov\Exchange\Fields\FieldInterface;
use Sholokhov\Exchange\MappingExchangeInterface;
use Sholokhov\Exchange\Repository\Map\MappingRegistry;

/**
 * Импорт значения в список
 *
 * @package Preparation
 */
abstract class AbstractEnumeration extends AbstractImport
{
    /**
     * Поддерживаемые ключи связи
     *
     * @var array|string[]
     */
    protected array $supportedPrimaries = ['VALUE', 'ID', 'XML_ID'];

    /**
     * Связующий ключ по умолчанию
     *
     * @var string
     */
    protected string $defaultPrimary = 'VALUE';

    /**
     * Нормализация результата импорта значения
     *
     * @param mixed $value
     * @param FieldInterface $field
     * @return int
     */
    protected function normalize(mixed $value, FieldInterface $field): int
    {
        return is_array($value) ? $this->normalize(reset($value), $field) : max((int)$value, 0);
    }

    /**
     * Конфигурация импорта файла
     *
     * @param ExchangeInterface $target
     * @return void
     * @throws Exception
     */
    protected function configurationTarget(ExchangeInterface $target): void
    {
        if ($this->logger) {
            $target->setLogger($this->logger);
        }

        if ($target instanceof MappingExchangeInterface) {
            $repository = new MappingRegistry;
            $repository->setFields([
                (new Field)
                    ->setFrom(0)
                    ->setTo($this->primary)
                    ->setPrimary(),
            ]);

            $target->setMappingRegistry($repository);
        }
    }
}