<?php

return [
    'TARGET_CODE' => 'map_field',
    'MAP_CODE' => 'map_field',
    'SETTINGS' => json_encode(
        [
            [
                'view' => 'checkbox',
                'name' => 'primary',
                'title' => 'Поле отвечает за идентификацию значений'
            ],
            [
                'view' => 'checkbox',
                'name' => 'hash',
                'title' => 'Свойство хранит хеш импорта'
            ],
            [
                'view' => 'checkbox',
                'name' => 'created_link',
                'title' => 'При отсутствии элемента производить его создание'
            ],
            [
                'view' => 'input',
                'name' => 'from',
                'title' => 'Путь до значения в источнике'
            ],
            [
                'view' => 'entity-selector',
                'name' => 'to',
                'title' => 'Куда сохранять значение'
            ]
        ]
    )
];