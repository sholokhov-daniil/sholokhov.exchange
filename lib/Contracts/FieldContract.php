<?php

namespace Sholokhov\Exchange\Contracts;

use Sholokhov\Exchange\Fields\FieldInterface;

/**
 * @method FieldInterface create(string $name, array $parameters = []) Создание объекта свойства
 */
class FieldContract extends AbstractGroupContainer
{
    /**
     * Код группы
     *
     * @var string
     */
    protected string $code = 'field';
}