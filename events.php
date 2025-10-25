<?php

use Bitrix\Main\EventManager;
use Sholokhov\Exchange\Builder\ModuleMenu;
use Sholokhov\Exchange\Events\Exchange\Import\HandbookHandler;
use Sholokhov\Exchange\Events\Exchange\IBlockHandler;
use Sholokhov\Exchange\Events\Exchange\Import\IBlock\EnumerationHandler;
use Sholokhov\Exchange\Events\Exchange\Import\Sale\WarehouseHandler;
use Sholokhov\Exchange\Helper\Helper;

$eventManager = EventManager::getInstance();

$eventManager->addEventHandler(
    'main',
    'OnBuildGlobalMenu',
    (new ModuleMenu)->make(...)
);

$eventManager->addEventHandler(
    Helper::getModuleID(),
    'onBeforeCreateValidator',
    WarehouseHandler::getValidators(...)
);

$eventManager->addEventHandler(
    Helper::getModuleID(),
    'onBeforeCreateValidator',
    IBlockHandler::getValidators(...)
);

$eventManager->addEventHandler(
    Helper::getModuleID(),
    'onBeforeCreateValidator',
    EnumerationHandler::getValidators(...)
);

$eventManager->addEventHandler(
    Helper::getModuleID(),
    'onBeforeCreateValidator',
    HandbookHandler::getValidators(...)
);