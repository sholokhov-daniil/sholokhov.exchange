<?php

namespace Sholokhov\Exchange\Facade;

use Closure;
use Iterator;

/**
 * Контейнер источник данных обмена
 *
 * @method static Iterator|null create(string $name, array $parameters = []) Создание источника данных
 * @method static void bind(string $name, string|Closure $concrete) Регистрация нового источника данных
 * @method static bool has(string $name) Проверка наличия источника данных
 */
class SourceFacade extends AbstractFacade
{
    public static function getFacadeAccessor(): string
    {
        return 'sourceContract';
    }
}
