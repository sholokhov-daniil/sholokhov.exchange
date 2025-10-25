<?php

use Sholokhov\Exchange\Bootstrap;
use Sholokhov\Exchange\Helper\Config;

@require_once 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

// Инициализация конфигураций
(new Bootstrap\Configuration)->bootstrap();
$bootstrapIterator = Config::get('bootstrap') ?: [];
array_walk(
    $bootstrapIterator,
    function(string $entity) {
        if (is_subclass_of($entity, Bootstrap\BootstrapInterface::class)) {
            (new $entity)->bootstrap();
        }
    }
);
unset($bootstrapIterator);

@require_once 'events.php';
