<?php

namespace Sholokhov\Exchange\Contracts;

use Sholokhov\Exchange\ExchangeInterface;

/**
 * @method ExchangeInterface create(string $name, array $parameters = []) Создание объекта свойства
 */
class TargetContract extends AbstractGroupContainer
{
    /**
     * Код группы
     *
     * @var string
     */
    protected string $code = 'target';
}