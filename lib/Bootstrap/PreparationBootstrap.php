<?php

namespace Sholokhov\Exchange\Bootstrap;

use Sholokhov\Exchange\Helper\Config;

/**
 * @inernal
 */
class PreparationBootstrap
{
    public function bootstrap(): void
    {
        $iterator = (array)Config::get('target')['preparations'];
    }
}