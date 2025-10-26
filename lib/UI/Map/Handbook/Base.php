<?php

namespace Sholokhov\Exchange\UI\Map\Handbook;

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\SystemException;
use Bitrix\Highloadblock\HighloadBlockTable;
use Sholokhov\Exchange\UI\EntitySelector\UserFieldProvider;

/**
 * Производит формирование данных свойств, для базового поля справочника
 */
class Base
{
    /**
     * Формирование данных
     *
     * @param int $entityId
     * @return array[]
     * @throws LoaderException
     */
    public function __invoke(int $entityId): array
    {
        if (!Loader::includeModule('highloadblock')) {
            return [];
        }

        return [
            'multiple' => false,
            'dialogOptions' => [
                'entities' => [
                    [
                        'id' => UserFieldProvider::ENTITY_ID,
                        'dynamicSearch' => true,
                        'dynamicLoad' => true,
                        'options' => [
                            'entityId' => HighloadBlockTable::compileEntityId($entityId)
                        ]
                    ]
                ]
            ],
        ];
    }
}