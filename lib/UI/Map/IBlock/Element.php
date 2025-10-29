<?php

namespace Sholokhov\Exchange\UI\Map\IBlock;

use Sholokhov\Exchange\UI\EntitySelector\IBlock\PropertyProvider;

/**
 * Производит формирование данных свойств, для базового поля справочника
 */
class Element
{
    /**
     * Формирование данных
     *
     * @param int $entityId
     * @return array[]
     */
    public function __invoke(int $entityId): array
    {
        return [
            'multiple' => false,
            'dialogOptions' => [
                'entities' => [
                    [
                        'id' => PropertyProvider::ENTITY_ID,
                        'dynamicSearch' => true,
                        'dynamicLoad' => true,
                        'options' => [
                            'iblockId' => $entityId,
                        ]
                    ]
                ]
            ],
        ];
    }
}