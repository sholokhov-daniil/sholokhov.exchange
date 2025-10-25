<?php

namespace Sholokhov\Exchange\Helper;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * @package Helper
 */
class Entity
{
    /**
     * Получение символьного кода сущности
     *
     * @param string|object $entity
     * @return string
     */
    public static function getCode(string|object $entity): string
    {
        if (is_object($entity)) {
            $entity = $entity::class;
        }

        $code = str_replace('\\', '_', $entity);
        return mb_strtolower($code);
    }

    /**
     * Получение атрибута объекта
     * @throws ReflectionException
     */
    public static function getAttribute(string|object $entity, string $attribute): ?object
    {
        return self::getAttributeByReflection(new ReflectionClass($entity), $attribute);
    }

    /**
     * Получение атрибута у текущего объекта или его родителя
     *
     * @param string|object $entity
     * @param string $attribute
     * @return object|null
     * @throws ReflectionException
     */
    public static function getAttributeChain(string|object $entity, string $attribute): ?object
    {
        $reflection = new ReflectionClass($entity);
        $fountAttribute = self::getAttributeByReflection($reflection, $attribute);

        if ($fountAttribute) {
            return $fountAttribute;
        }

        while ($reflection = $reflection->getParentClass()) {
            if ($fountAttribute = self::getAttributeByReflection($reflection, $attribute)) {
                return $fountAttribute;
            }
        }

        return null;
    }

    /**
     * Получение атрибута метода
     *
     * @param ReflectionMethod $method
     * @param string $attribute
     * @return object|null
     */
    public static function getAttributeByMethod(ReflectionMethod $method, string $attribute): ?object
    {
        $attribute = $method->getAttributes($attribute)[0] ?? null;
        return $attribute?->newInstance();
    }

    /**
     * Получение атрибута из описания класса
     *
     * @param ReflectionClass $reflection
     * @param string $attribute
     * @return object|null
     */
    public static function getAttributeByReflection(ReflectionClass $reflection, string $attribute): ?object
    {
        $attribute = $reflection->getAttributes($attribute)[0] ?? null;
        return $attribute?->newInstance();
    }
}