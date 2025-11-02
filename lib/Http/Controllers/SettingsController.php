<?php

namespace Sholokhov\Exchange\Http\Controllers;

use Bitrix\Main\Diag\Debug;
use Exception;
use Sholokhov\Exchange\ORM\Settings\ExchangeTable;
use Throwable;

use Sholokhov\Exchange\Http\Middleware\ModuleRightMiddleware;
use Sholokhov\Exchange\ORM\Settings\EntityTable;
use Sholokhov\Exchange\ORM\UI\EntityUITable;

use Bitrix\Main\Error;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Localization\Loc;

/**
 * @internal
 */
final class SettingsController extends Controller
{
    /**
     * Конфигурация обработчиков запроса
     *
     * @return array[]
     */
    public function configureActions(): array
    {
        return [
            'save' => [
                '+prefilters' => [new ModuleRightMiddleware],
            ],
            'create' => [
                '+prefilters' => [new ModuleRightMiddleware],
            ]
        ];
    }

    /**
     * Создание настроек
     *
     * @param array $fields
     * @return void
     * @throws Exception
     */
    public function createAction(array $fields): void
    {
        try {
            $result = ExchangeTable::add(
                [
                    ExchangeTable::PC_ACTIVE => (bool)$fields['general']['active'],
                    ExchangeTable::PC_HASH => (string)$fields['general']['hash'],
                    ExchangeTable::PC_TARGET => (array)$fields['target'],
                    ExchangeTable::PC_SOURCE => (array)$fields['source'],
                    ExchangeTable::PC_MAP => (array)$fields['map'],
                ]
            );

            if (!$result->isSuccess()) {
                $this->addErrors($result->getErrors());
            }
        } catch (Throwable) {
            $this->addError(new Error('Ошибка создания настроек'));
        }
    }

    /**
     * Получение сущностей по типу
     *
     * @param string $code
     * @return array
     */
    public function saveAction(string $code): array
    {
        $result = [];

        try {
            $result = EntityTable::getList([
                'filter' => [
                    EntityTable::PC_TYPE_CODE => $code
                ],
                'cache' => ['ttl' => 36000]
            ])->fetchAll();
        } catch (Throwable) {
            $this->addError(new Error(Loc::getMessage('SHOLOKHOV_EXCHANGE_CONTROLLER_ENTITY_EXCEPTION'), 500));
        }

        return $result;
    }
}