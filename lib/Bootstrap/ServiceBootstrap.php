<?php

namespace Sholokhov\Exchange\Bootstrap;

use Sholokhov\Exchange\Container\Container;

use Bitrix\Main\DI\ServiceLocator;
use Sholokhov\Exchange\Helper\Config;

/**
 * Регистрация системных
 *
 * @internal
 */
class ServiceBootstrap implements BootstrapInterface
{
    /**
     * @return void
     */
    public function bootstrap(): void
    {
        $container = ServiceLocator::getInstance();
        $container->addInstance('sholokhov.exchange.container', new Container);

        $iterator = Config::get('service');
        foreach ($iterator as $entity) {
            $service = new $entity;
            $service();
        }
    }
}