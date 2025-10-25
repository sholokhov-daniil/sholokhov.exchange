<?php

namespace Sholokhov\Exchange\Preparation\Base;

use Exception;

use Sholokhov\Exchange\MappingExchangeInterface;
use Sholokhov\Exchange\Preparation\IBlock\PropertyTrait;

use Sholokhov\Exchange\Fields\Field;
use Sholokhov\Exchange\ExchangeInterface;
use Sholokhov\Exchange\Repository\Map\MappingRegistry;

/**
 * Преобразует значение имеющего связь к иной сущности
 *
 * @package Preparation
 */
abstract class AbstractIBlockImport extends AbstractImport
{
    use PropertyTrait;

    /**
     * Связующий ключ по умолчанию
     *
     * @var string
     */
    protected string $defaultPrimary = 'XML_ID';

    /**
     * @param string|null $primary Ключ по которому будет производиться проверка уникальности
     */
    public function __construct(int $iblockId, string $primary = null)
    {
        $this->iblockId = $iblockId;
        parent::__construct($primary);
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
            $mapping = new MappingRegistry;
            $mapping->setFields([
                (new Field)
                    ->setFrom(0)
                    ->setTo($this->primary)
                    ->setPrimary(),
            ]);

            $target->setMappingRegistry($mapping);
        }
    }
}