<?php

use Sholokhov\Exchange\UI\Map;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

return [
    'target' => [
        [
            'entity' => 'target.export.xml',
            'name' => 'SHOLOKHOV_EXCHANGE_TARGET_EXPORT_XML_NAME',
            'fields' => [
                'field.base',
            ],
        ],
        [
            'entity' => 'target.import.hl.element',
            'name' => 'SHOLOKHOV_EXCHANGE_TARGET_HL_ELEMENT_NAME',
            'fields' => [
                'field.base',
            ],
            'field_options' => [
                'default' => Map\Handbook\Base::class,
            ]
        ],
        [
            'entity' => 'target.import.iblock.element',
            'name' => 'SHOLOKHOV_EXCHANGE_TARGET_IBLOCK_ELEMENT_NAME',
            'fields' => [
                'field.base',
            ],
            'field_options' => [
                'default' => Map\IBlock\Element::class,
            ]
        ],
        [
            'entity' => 'target.import.catalog.product.simple',
            'name' => 'SHOLOKHOV_EXCHANGE_TARGET_IBLOCK_ELEMENT_SIMPLE_PRODUCT_NAME',
            'fields' => [
                'field.base',
            ],
            'field_options' => [
                'default' => Map\IBlock\Element::class,
            ]
        ],
        [
            'entity' => 'target.import.iblock.props.enum',
            'name' => 'SHOLOKHOV_EXCHANGE_TARGET_IBLOCK_PROPERTY_ENUM_VALUE_NAME',
            'fields' => [
                'field.base',
            ],
        ],
        [
            'entity' => 'taregt.import.iblock.section',
            'name' => 'SHOLOKHOV_EXCHANGE_TARGET_IBLOCK_SECTION_NAME',
            'fields' => [
                'field.base',
            ],
        ],
        [
            'entity' => 'target.import.uf.enum',
            'name' => 'SHOLOKHOV_EXCHANGE_TARGET_UF_ENUM_VALUE_NAME',
            'fields' => [
                'field.base',
            ],
        ],
    ],
    'source' => [
        [
            'entity' => 'source.xml.db',
            'name' => 'SHOLOKHOV_EXCHANGE_SOURCE_DB_XML_NAME',
            'description' => 'SHOLOKHOV_EXCHANGE_SOURCE_DB_XML_DESC'
        ],
        [
            'entity' => 'source.iblock.element',
            'name' => 'SHOLOKHOV_EXCHANGE_SOURCE_ELEMENT_IBLOCK_NAME',
        ],
        [
            'entity' => 'source.csv',
            'name' => 'SHOLOKHOV_EXCHANGE_SOURCE_SIMPLE_CSV_NAME',
            'description' => 'SHOLOKHOV_EXCHANGE_SOURCE_SIMPLE_CSV_DESC',
        ],
        [
            'entity' => 'source.json.file',
            'name' => 'SHOLOKHOV_EXCHANGE_SOURCE_SIMPLE_JSON_NAME',
            'description' => 'SHOLOKHOV_EXCHANGE_SOURCE_SIMPLE_JSON_DESC',
        ],
        [
            'entity' => 'source.xml.simple',
            'name' => 'SHOLOKHOV_EXCHANGE_SOURCE_SIMPLE_XML_NAME',
            'description' => 'SHOLOKHOV_EXCHANGE_SOURCE_SIMPLE_XML_DESC',
        ],
    ],
    'fields' => [
        [
            'entity' => 'field.base',
            'name' => 'SHOLOKHOV_EXCHANGE_MAP_FIELD_NAME'
        ]
    ]
];