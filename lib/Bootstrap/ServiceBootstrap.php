<?php

namespace Sholokhov\Exchange\Bootstrap;

use Bitrix\Main\DI\ServiceLocator;
use Sholokhov\Exchange\Container\Container;

/**
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
        $container->addInstance('sholokhov.exchange.mapContainer', new Container);
        $container->addInstance('sholokhov.exchange.sourceContainer', new Container);
        $container->addInstance('sholokhov.exchange.targetContainer', new Container);
    }
}