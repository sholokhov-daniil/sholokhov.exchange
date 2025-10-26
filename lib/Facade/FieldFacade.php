<?php

namespace Sholokhov\Exchange\Facade;

use Closure;
use Sholokhov\Exchange\Fields\FieldInterface;

/**
 * Контейнер описаний обмена(карты)
 *
 * @method static FieldInterface|null create(string $name, array $parameters = []) Создание свойства описывающего обмен
 * @method static void bind(string $name, string|Closure $concrete) Регистрация нового свойства описываюзего обмен
 * @method static bool has(string $name) Проверка наличия свойства
 */
class FieldFacade extends AbstractFacade
{
    public static function getFacadeAccessor(): string
    {
        return 'fieldContract';
    }
}
