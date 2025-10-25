<?php

namespace Sholokhov\Exchange\Factory\Highloadblock;

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\SystemException;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Highloadblock\HighloadBlockTable as HLT;

class ProviderFactory
{
    /**
     * Создание провайдера данных справочника на основе ID
     *
     * @param string $table
     * @return DataManager|string|null
     * @throws LoaderException
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function createByTable(string $table): DataManager|string|null
    {
        return self::createFromFilter(['TABLE_NAME' => $table]);
    }

    /**
     * Создание провайдера справочника на основе фильтра
     *
     * @param array $filter
     * @return DataManager|string|null
     * @throws LoaderException
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function createFromFilter(array $filter): DataManager|string|null
    {
        if (!Loader::includeModule('highloadblock')) {
            throw new LoaderException('Module "highloadblock" is not installed');
        }

        $result = HLT::query()
            ->setFilter($filter)
            ->setCacheTtl(36000)
            ->addSelect('ID')
            ->exec()
            ->fetch();

        if (!$result) {
            return null;
        }

        return HLT::compileEntity($result['ID'])?->getDataClass() ?: null;
    }
}