<?php

namespace Sholokhov\Exchange\Facade;

use RuntimeException;
use Sholokhov\Exchange\Helper\Helper;

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\ObjectNotFoundException;

use Psr\Container\NotFoundExceptionInterface;

abstract class AbstractFacade
{
    /**
     * Наименование зарегистрированного наименования компонента
     *
     * @return string
     */
    abstract public static function getFacadeAccessor(): string;

    /**
     * Получение объекта описывающий фасадом
     *
     * @return object|null
     * @throws ObjectNotFoundException
     * @throws NotFoundExceptionInterface
     */
    public static function getFacadeRoot(): ?object
    {
        $accessor = Helper::getModuleID() . '.' . static::getFacadeAccessor();
        return ServiceLocator::getInstance()->get($accessor);
    }

    /**
     * Обрабатывать динамические и статические вызовы к объекту.
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     * @throws NotFoundExceptionInterface
     * @throws ObjectNotFoundException
     */
    public static function __callStatic(string $method, array $arguments)
    {
        $instance = static::getFacadeRoot();

        if (!$instance) {
            throw new RuntimeException('A facade root has not been set.');
        }

        return $instance->$method(...$arguments);
    }
}