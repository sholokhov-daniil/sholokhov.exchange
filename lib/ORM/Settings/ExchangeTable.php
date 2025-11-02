<?php

namespace Sholokhov\Exchange\ORM\Settings;

use CUser;

use Sholokhov\Exchange\Helper\Json;

use Bitrix\Main\ORM\Fields;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UserTable;

/**
 * Настройки обменов
 */
final class ExchangeTable extends DataManager
{
    /**
     * Уникальный текстовый идентификатор обмена
     */
    public const PC_HASH = "HASH";

    /**
     * Активность обмена
     */
    public const PC_ACTIVE = "ACTIVE";

    /**
     * Наименование обмена
     */
    public const PC_NAME = "NAME";

    /**
     * Описание обмена
     */
    public const PC_DESCRIPTION = "DESCRIPTION";

    /**
     * Настройки источника данных
     */
    public const PC_SOURCE = "SOURCE";

    /**
     * Настройки способа обмена
     */
    public const PC_TARGET = "TARGET";

    /**
     * Карта обмена
     */
    public const PC_MAP = "MAP";

    /**
     * Дата создания обмена
     */
    public const PC_DATE_CREATE = "DATE_CREATE";

    /**
     * Дата обновления настроек обмена
     */
    public const PC_DATE_UPDATE = "DATE_UPDATE";

    /**
     * Пользователь создавший настройки обмена
     */
    public const PC_USER_ID_CREATED = "USER_ID_CREATED";

    /**
     * Пользователь обновивший настройки обмена
     */
    public const PC_USER_ID_UPDATED = "USER_ID_UPDATED";

    /**
     * Связь с таблицей, где хранится пользователь создавший настройки обмена
     */
    public const PC_USER_CREATED = "USER_CREATED";

    /**
     * Связь с таблицей, где хранится пользователь обновивший настройки обмена
     */
    public const PC_USER_UPDATED = "USER_UPDATED";

    /**
     * @return string
     */
    public static function getTableName(): string
    {
        return 'sholokhov_exchange_settings';
    }

    /**
     * @return array
     *
     * @throws SystemException
     */
    public static function getMap(): array
    {
        return [
            (new Fields\StringField(self::PC_HASH))
                ->configurePrimary(),

            (new Fields\BooleanField(self::PC_ACTIVE))
                ->configureRequired()
                ->configureDefaultValue(true),

            (new Fields\StringField(self::PC_NAME))
                ->configureSize(255)
                ->configureDefaultValue(''),

            (new Fields\StringField(self::PC_DESCRIPTION))
                ->configureDefaultValue(''),

            (new Fields\StringField(self::PC_SOURCE))
                ->addFetchDataModifier(Json::decode(...))
                ->addSaveDataModifier(Json::encode(...))
                ->configureRequired(),

            (new Fields\StringField(self::PC_TARGET))
                ->addFetchDataModifier(Json::decode(...))
                ->addSaveDataModifier(Json::encode(...))
                ->configureRequired(),

            (new Fields\StringField(self::PC_MAP))
                ->addFetchDataModifier(Json::decode(...))
                ->addSaveDataModifier(Json::encode(...))
                ->configureRequired(),

            (new Fields\DatetimeField(self::PC_DATE_CREATE))
                ->configureRequired()
                ->configureDefaultValue(new DateTime),

            (new Fields\DatetimeField(self::PC_DATE_UPDATE))
                ->configureRequired()
                ->configureDefaultValue(new DateTime)
                ->addSaveDataModifier(fn() => new DateTime),

            (new Fields\IntegerField(self::PC_USER_ID_CREATED))
                ->configureRequired()
                ->configureDefaultValue((new CUser)->GetID()),

            (new Fields\IntegerField(self::PC_USER_ID_UPDATED))
                ->configureRequired()
                ->configureDefaultValue((new CUser)->GetID())
                ->addSaveDataModifier(fn() => (int)(new CUser)->GetID()),

            (new Fields\Relations\Reference(
                self::PC_USER_CREATED,
                UserTable::class,
                Join::on('this.' . self::PC_USER_ID_CREATED, 'ref.ID')
            )),

            (new Fields\Relations\Reference(
                self::PC_USER_UPDATED,
                UserTable::class,
                Join::on('this.' . self::PC_USER_UPDATED, 'ref.ID')
            )),
        ];
    }
}