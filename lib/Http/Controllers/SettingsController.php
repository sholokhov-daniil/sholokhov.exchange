<?php

namespace Sholokhov\Exchange\Http\Controllers;

use Bitrix\Main\Diag\Debug;
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

    public function createAction(array $fields): void
    {
        Debug::dumpToFile($fields);
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