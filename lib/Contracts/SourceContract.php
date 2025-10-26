<?php

namespace Sholokhov\Exchange\Contracts;

use Iterator;

/**
 * @method Iterator create(string $name, array $parameters = []) Создание объекта свойства
 */
class SourceContract extends AbstractGroupContainer
{
    /**
     * Код группы
     *
     * @var string
     */
    protected string $code = 'source';
}