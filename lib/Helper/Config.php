<?php

namespace Sholokhov\Exchange\Helper;

use Bitrix\Main\Config\Configuration;

/**
 * @internal
 */
class Config
{
    /**
     * Получение значения по идентификатору
     *
     * @param string $name
     * @return mixed
     */
    public static function get(string $name): mixed
    {
        return self::registry()->get($name);
    }

    /**
     * Добавить значение в хранилище
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public static function add(string $name, mixed $value): void
    {
        self::registry()->add($name, $value);
    }

    /**
     * Получение хранилища конфигураций
     *
     * @return Configuration
     */
    public static function registry(): Configuration
    {
        return Configuration::getInstance(Helper::getModuleID());
    }
}