<?php

namespace Sholokhov\Exchange\Contracts;

use Closure;
use Exception;
use ReflectionException;

use Sholokhov\Exchange\Container\Container;
use Bitrix\Main\ObjectNotFoundException;

use Psr\Container\NotFoundExceptionInterface;

/**
 * Групповой контейнер сервисов
 *
 * Все сервисы хранятся в общем контейнере {@see Container},
 * но код сервиса модифицируется на основе текущей группы {@see self::$code}
 *
 * @internal
 */
abstract class AbstractGroupContainer
{
    /**
     * Код группы
     *
     * @var string
     */
    protected string $code = '';

    /**
     * Создание объекта
     *
     * @param string $name
     * @param array $parameters
     * @return object|null
     * @throws ObjectNotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function create(string $name, array $parameters = []): ?object
    {
        $name = $this->modifyName($name);
        return Container::getInstance()?->create($name, $parameters);
    }

    /**
     * Регистрирование объекта
     *
     * @param string $name
     * @param Closure|string $concrete
     * @return void
     * @throws NotFoundExceptionInterface
     * @throws ObjectNotFoundException
     * @throws Exception
     */
    public function bind(string $name, Closure|string $concrete): void
    {
        $name = $this->modifyName($name);
        Container::getInstance()?->bind($name, $concrete);
    }

    /**
     * Проверка наличия объекта
     *
     * @param string $name
     * @return bool
     * @throws NotFoundExceptionInterface
     * @throws ObjectNotFoundException
     */
    public function has(string $name): bool
    {
        $name = $this->modifyName($name);
        return Container::getInstance()?->has($name);
    }

    /**
     * Модификация ключа объекта
     *
     * @param string $name
     * @return string
     */
    final protected function modifyName(string $name): string
    {
        return $this->code . '.' . $name;
    }
}