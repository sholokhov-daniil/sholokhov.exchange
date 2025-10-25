<?php

namespace Sholokhov\Exchange\Facade;

/**
 * Контейнер обменов
 */
class TargetContainer extends AbstractFacade
{
    public static function getFacadeAccessor(): string
    {
        return 'targetContainer';
    }
}
