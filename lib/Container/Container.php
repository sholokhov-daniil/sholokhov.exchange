<?php

namespace Sholokhov\Exchange\Container;

use Closure;
use Exception;
use ReflectionClass;
use ReflectionException;

/**
 * Контейнер объектов модуля
 */
class Container
{
    /**
     * Привязанные реализации
     *
     * @var Closure|string[]
     */
    protected array $bindings = [];

    /**
     * Инициализированные реализации
     *
     * @var array
     */
    protected array $instances = [];

    /**
     * Получение реализации по названию.
     * Если она не инициализирована, то производится его инициализация с сохранением состояния,
     * для исключения повторной инициализации.
     *
     * @param string $name
     * @return object|null
     * @throws ReflectionException
     */
    public function get(string $name): ?object
    {
        if (!$this->has($name)) {
            return null;
        }

        if (isset($this->instances[$name])) {
            return $this->instances[$name];
        }

        return $this->instances[$name] = $this->build($name);
    }

    /**
     * Проверка наличия связи с реализацией
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->bindings[$name]);
    }

    /**
     * Инициализация объекта, для использования как синглтон
     *
     * @param string $name
     * @param string|Closure $concrete
     * @return Container
     * @throws Exception
     */
    public function bind(string $name, string|Closure $concrete): static
    {
        if (! $concrete instanceof Closure) {
            if (is_callable($concrete)) {
                $concrete = Closure::fromCallable($concrete);
            } elseif (!class_exists($concrete)) {
                throw new Exception('Invalid concrete class');
            }
        }

        $this->bindings[$name] = $concrete;

        return $this;
    }

    /**
     * Создание нового экземпляра класса
     *
     * @param string $name
     * @param array $parameters
     * @return object|null
     * @throws ReflectionException
     */
    public function create(string $name, array $parameters = []): ?object
    {
        if (isset($this->instances[$name])) {
            return $this->instances[$name];
        }

        $concrete = $this->getConcrete($name);

        if ($concrete instanceof Closure) {
            return call_user_func($concrete);
        }

        return $this->build($concrete, $parameters);
    }

    /**
     * Получение по наименованию
     *
     * @param string $alias
     * @return mixed
     */
    protected function getConcrete(string $alias): mixed
    {
        return $this->bindings[$alias] ?? null;
    }

    /**
     * Создание объекта с пользовательскими параметрами
     *
     * @param string $name
     * @param array $parameters
     * @return object
     * @throws ReflectionException
     */
    protected function build(string $name, array $parameters = []): object
    {
        $reflector = new ReflectionClass($name);
        return $reflector->newInstanceArgs($parameters);
    }
}
