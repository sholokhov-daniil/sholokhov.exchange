<?php

namespace Sholokhov\Exchange\Facade;

use Closure;
use Sholokhov\Exchange\ExchangeInterface;

/**
 * Контейнер обменов
 *
 * @method static ExchangeInterface|null create(string $name, array $parameters = []) Создание обмена
 * @method static void bind(string $name, string|Closure $concrete) Регистрация нового обмена
 * @method static bool has(string $name) Проверка наличия обмена
 */
class TargetContainer extends AbstractFacade
{
    public static function getFacadeAccessor(): string
    {
        return 'targetContract';
    }
}
