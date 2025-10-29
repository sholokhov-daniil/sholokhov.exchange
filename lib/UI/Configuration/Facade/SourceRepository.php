<?php

namespace Sholokhov\Exchange\UI\Configuration\Facade;

use Sholokhov\Exchange\Facade\AbstractFacade;
use Sholokhov\Exchange\UI\Configuration\Entity\EntityConfig;

/**
 * @method static EntityConfig get(string $id) Получение конфигурации, для определенного источника данных
 * @method static bool has(string $id) Проверка наличия конфигурации источника данных
 * @method static EntityConfig[] getAll() Все зарегистрированные конфигурации
 */
class SourceRepository extends AbstractFacade
{
    public static function getFacadeAccessor(): string
    {
        return 'ui.source.repository';
    }
}