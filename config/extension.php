<?php

use Sholokhov\Exchange\Helper\UI;

return [
    'sholokhov.exchange.detail' => [
        'js' => UI::getJs('detail'),
        'css' => UI::getCss('detail'),
        'lang' => UI::getLang('detail'),
        'rel' => [
            'ui.buttons',
        ],
    ]
];
