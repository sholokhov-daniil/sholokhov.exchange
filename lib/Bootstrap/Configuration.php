<?php

namespace Sholokhov\Exchange\Bootstrap;

use DirectoryIterator;
use Sholokhov\Exchange\Helper\Helper;
use Sholokhov\Exchange\Helper\Config;

/**
 * Производит инициализацию конфигураций модуля
 */
class Configuration implements BootstrapInterface
{
    /**
     * Выполнить автозагрузку
     *
     * @return void
     */
    public function bootstrap(): void
    {
        $iterator = new DirectoryIterator(Helper::getRootDir() . DIRECTORY_SEPARATOR . 'config');

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->isReadable()) {
                Config::add($file->getBasename('.php'), @include $file->getPathname());
            }
        }
    }
}