<?php

namespace Sholokhov\Exchange\Http\Controllers;

use Sholokhov\Exchange\Container\Container;
use Sholokhov\Exchange\UI\Configuration\Repository\EntityRepository;
use Throwable;

use Sholokhov\Exchange\Http\Middleware\ModuleRightMiddleware;
use Sholokhov\Exchange\ORM\UI\EntityUITable;

use Bitrix\Main\Error;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Localization\Loc;

/**
 * @internal
 */
final class EntityController extends Controller
{
    /**
     * Конфигурация обработчиков запроса
     *
     * @return array[]
     */
    public function configureActions(): array
    {
        return [
            'getByType' => [
                '+prefilters' => [new ModuleRightMiddleware],
            ],
            'getFields' => [
                '+prefilters' => [new ModuleRightMiddleware],
            ],
        ];
    }

    /**
     * Получение сущностей по типу
     *
     * @param string $code
     * @return array
     */
    public function getByTypeAction(string $code): array
    {
        $result = [];

        try {
            /** @var EntityRepository $repository */
            $repository = Container::getInstance()->get("ui.$code.repository");
            $iterator = $repository->getAll();

            foreach ($iterator as $config) {
                $result[] = [
                    'entity' => $config->getEntity(),
                    'name' => $config->getName(),
                    'description' => $config->getDescription(),
                ];
            }
        } catch (Throwable) {
            $this->addError(new Error(Loc::getMessage('SHOLOKHOV_EXCHANGE_CONTROLLER_ENTITY_EXCEPTION'), 500));
        }

        return $result;
    }

    /**
     * Получение доступных полей сущности
     *
     * @param string $code
     * @return array
     */
    public function getFieldsAction(string $code): array
    {
        $result = [];

        try {
            $row = EntityUITable::getRow([
                'filter' => [
                    EntityUITable::PC_ENTITY_CODE => $code
                ],
                'cache' => ['ttl' => 36000]
            ]);

            if ($row) {
                $result = $row[EntityUITable::PC_SETTINGS];
            }
        } catch (Throwable) {
            $this->addError(new Error(Loc::getMessage('SHOLOKHOV_EXCHANGE_CONTROLLER_ENTITY_EXCEPTION'), 500));
        }

        return $result;
    }
}