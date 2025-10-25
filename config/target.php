<?php

use Sholokhov\Exchange\Target;
use Sholokhov\Exchange\Preparation;

return [
    'preparation' => [
        Target\Export\Xml::class => Preparation\XmlFieldPreparationPipeline::class,
        'default' => Preparation\FieldPreparationPipeline::class,
    ]
];