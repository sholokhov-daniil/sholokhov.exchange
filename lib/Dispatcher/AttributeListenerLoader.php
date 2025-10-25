<?php

namespace Sholokhov\Exchange\Dispatcher;

use ReflectionClass;
use ReflectionException;

use Sholokhov\Exchange\Helper\Entity;
use Sholokhov\Exchange\Target\Attributes\Event;

/**
 * Производит загрузку событий объектаи его родителей
 */
class AttributeListenerLoader
{
    public function __construct(private readonly EntityEventProvider $provider)
    {
    }

    /**
     * Загрузка событий
     *
     * @param object $entity
     * @return void
     * @throws ReflectionException
     */
    public function load(object $entity): void
    {
        foreach ($this->getEntityChain($entity) as $candidate) {
            $this->registerMethods($candidate, $entity);
        }
    }

    /**
     * Регистрация событий на основе методов объекта
     *
     * @param string $className
     * @param object $instance
     * @return void
     * @throws ReflectionException
     */
    private function registerMethods(string $className, object $instance): void
    {
        $reflection = new ReflectionClass($className);

        foreach ($reflection->getMethods() as $method) {
            /** @var Event|null $attribute */
            $attribute = Entity::getAttributeByMethod($method, Event::class);
            if (!$attribute) {
                continue;
            }

            $context = $method->isStatic() ? null : $instance;
            $listener = $method->getClosure($context);

            $this->provider->subscribe($attribute->getType()->value, $listener);
        }
    }

    /**
     * Получение цепочки наследований объекта
     *
     * @param object $entity
     * @return array
     */
    private function getEntityChain(object $entity): array
    {
        $chain = array_reverse(class_parents($entity));
        $chain[] = $entity::class;

        return $chain;
    }
}