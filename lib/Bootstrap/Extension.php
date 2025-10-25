<?php

namespace Sholokhov\Exchange\Bootstrap;

use CJSCore;
use Sholokhov\Exchange\Helper\Config;

/**
 * @internal
 */
class Extension implements BootstrapInterface
{
    /**
     * Выполнить загрузку
     *
     * @return void
     */
    public function bootstrap(): void
    {
        $iterator = Config::get('extension') ?: [];

        if (!is_array($iterator)) {
            return;
        }

        array_walk($iterator, fn($options, $name) => CJSCore::RegisterExt($name, $options));
    }
}