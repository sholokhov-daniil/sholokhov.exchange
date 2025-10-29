<?php

namespace Sholokhov\Exchange\UI\Configuration\Facade;

use Sholokhov\Exchange\Facade\AbstractFacade;
use Sholokhov\Exchange\UI\Configuration\Entity\TargetConfig;

/**
 * @method static TargetConfig get(string $id) Получение конфигурации, для определенного обмена
 * @method static bool has(string $id) Проверка наличия конфигурации, для обмена
 * @method static TargetConfig[] getAll() Все зарегистрированные конфигурации
 */
class TargetRepository extends AbstractFacade
{
    public static function getFacadeAccessor(): string
    {
        return 'ui.target.repository';
    }
}