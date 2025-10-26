<?php

namespace Sholokhov\Exchange\Facade;

use ReflectionException;
use RuntimeException;

use Sholokhov\Exchange\Container\Container;

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
     * @throws NotFoundExceptionInterface
     * @throws ObjectNotFoundException
     * @throws ReflectionException
     */
    public static function getFacadeRoot(): ?object
    {
        return Container::getInstance()->get(static::getFacadeAccessor());
    }

    /**
     * Обрабатывать динамические и статические вызовы к объекту.
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     * @throws NotFoundExceptionInterface
     * @throws ObjectNotFoundException
     * @throws ReflectionException
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