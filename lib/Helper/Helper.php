<?php

namespace Sholokhov\Exchange\Helper;

use Illuminate\Support\Arr;

/**
 * @package Helper
 */
class Helper
{
    /**
     * ID текущего модуля
     *
     * @return string
     */
    public static function getModuleID(): string
    {
        return GetModuleID(self::getRootDir());
    }

    /**
     * Путь до корня модуля
     *
     * @return string
     */
    public static function getRootDir(): string
    {
        return dirname(__DIR__, 2);
    }

    /**
     * Получение значения по пути из ключей массива
     *
     * @param array $item
     * @param string $path
     * @return mixed
     */
    public static function getArrValueByPath(array $item, $path): mixed
    {
        return Arr::get($item, $path);
    }
}