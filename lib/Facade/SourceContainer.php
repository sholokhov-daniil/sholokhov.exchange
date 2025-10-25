<?php

namespace Sholokhov\Exchange\Facade;

use Closure;
use Sholokhov\Exchange\Container\Container;

/**
 * Контейнер источник данных обмена
 *
 * @method static object|null create(string $name, array $parameters = []) Создание источника данных
 * @method static Container bind(string $name, string|Closure $concrete) Регистрация нового источника данных
 * @method static bool has(string $name) Проверка наличия источника данных
 */
class SourceContainer extends AbstractFacade
{
    public static function getFacadeAccessor(): string
    {
        return 'sourceContainer';
    }
}
