<?php

namespace Sholokhov\Exchange\Facade;

use Closure;
use Sholokhov\Exchange\Container\Container;

/**
 * Контейнер описаний обмена(карты)
 *
 * @method static object|null create(string $name, array $parameters = []) Создание свойства описывающего обмен
 * @method static Container bind(string $name, string|Closure $concrete) Регистрация нового свойства описываюзего обмен
 * @method static bool has(string $name) Проверка наличия свойства
 */
class FieldContainer extends AbstractFacade
{
    public static function getFacadeAccessor(): string
    {
        return 'mapContainer';
    }
}
