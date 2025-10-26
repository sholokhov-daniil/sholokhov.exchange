<?php

namespace Sholokhov\Exchange\ORM\Settings;

use Bitrix\Main\ORM\Fields;
use Bitrix\Main\ORM\Data\DataManager;

/**
 * Хранит доступные типы сущностей
 *
 * @final
 */
final class EntityTypeTable extends DataManager
{
    /**
     * Уникальный символьный код типа сущности
     */
    public const PC_CODE = "CODE";

    /**
     * Название таблицы
     *
     * @return string
     */
    public static function getTableName(): string
    {
        return 'sholokhov_exchange_entity_type';
    }

    /**
     * Описание столбцов с данными
     *
     * @return array
     */
    public static function getMap(): array
    {
        return [
            (new Fields\StringField(self::PC_CODE))
                ->configurePrimary()
                ->configureRequired(),
        ];
    }
}