<?php

use Bitrix\Main\IO\Directory;
use Bitrix\Main\Loader;
use Bitrix\Main\Application;
use Bitrix\Main\DB\Connection;
use Bitrix\Main\EventManager;
use Bitrix\Main\Localization\Loc;

use Sholokhov\Exchange\ORM\ResultTable;
use Sholokhov\Exchange\ORM\Settings\EntityTable;
use Sholokhov\Exchange\ORM\Settings\EntityTypeTable;
use Sholokhov\Exchange\ORM\Settings\ExchangeTable;
use Sholokhov\Exchange\ORM\UI\TargetMapTable;

class sholokhov_exchange extends CModule
{
    var $MODULE_ID = "sholokhov.exchange";
    var $PARTNER_NAME = 'Шолохов Даниил';
    var $PARTNER_URI = 'https://github.com/sholokhov-daniil';

    private const PHP_VERSION = '8.2.0';

    /**
     * @var class-string<\Bitrix\Main\ORM\Data\DataManager>[]
     */
    private array $orm = [
        ResultTable::class,
        EntityTypeTable::class,
        EntityTable::class,
        ExchangeTable::class,
        TargetMapTable::class,
    ];

    public function __construct()
    {
        $arModuleVersion = [];

        include(__DIR__ . DIRECTORY_SEPARATOR . "version.php");
        if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        } else {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }

        $this->MODULE_NAME = Loc::getMessage("SHOLOKHOV_EXCHANGE_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("SHOLOKHOV_EXCHANGE_MODULE_DESCRIPTION");
    }

    public function DoInstall(): bool
    {
        global $APPLICATION;

        try {
            $this->checkPhpVersion();
            $this->checkComposer();
            $this->InstallDB();
            $this->InstallFiles();
        } catch (Throwable $exception) {
            $APPLICATION->ThrowException($exception->getMessage());
            return false;
        }

        return true;

    }

    public function DoUninstall(): void
    {
        $this->UnInstallDB();
        
        $this->UnInstallFiles();
        $this->Remove();
    }

    public function InstallFiles(): void
    {
        $root = Loader::getDocumentRoot();

        CopyDirFiles(
            __DIR__ . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'admin',
            $root . DIRECTORY_SEPARATOR . 'bitrix' . DIRECTORY_SEPARATOR . 'admin',
            true,
            true
        );

        CopyDirFiles(
            __DIR__ . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'components',
            $root . DIRECTORY_SEPARATOR . 'bitrix' . DIRECTORY_SEPARATOR . 'components',
            true,
            true
        );
    }

    public function UnInstallFiles(): void
    {
        $root = Loader::getDocumentRoot();

        DeleteDirFiles(
            __DIR__ . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'admin',
            $root . DIRECTORY_SEPARATOR . 'bitrix' . DIRECTORY_SEPARATOR . 'admin'
        );

        Directory::deleteDirectory($root . DIRECTORY_SEPARATOR . 'bitrix' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'sholokhov');
    }

    public function InstallDB(): void
    {
        $this->registrationEvents();
        $this->Add();

        self::IncludeModule($this->MODULE_ID);

        $this->dropTables();

        $connection = Application::getConnection();
        foreach ($this->orm as $orm) {
            $tableName = $orm::getTableName();

            if ($connection->isTableExists($tableName)) {
                $connection->dropTable($tableName);
            }

            $orm::getEntity()->createDbTable();
        }

        $this->migration();
    }

    public function UnInstallDB(): void
    {
        $this->unRegistrationEvents();
        $this->dropTables();
        $this->Remove();
    }

    private function migration(): void
    {
        $this->fillTable(EntityTypeTable::class, $this->getConfigPath('types'));
        $this->fillTable(EntityTable::class, $this->getConfigPath('entities'));
        $this->fillTable(TargetMapTable::class, $this->getConfigPath('map'));
    }

    /**
     * @param class-string<\Bitrix\Main\ORM\Data\DataManager> $dataManager
     * @param string $path
     * @return void
     */
    private function fillTable(string $dataManager, string $path): void
    {
        $this->migrationFromConfig(
            $path,
            fn(array $config) => $dataManager::add($config)
        );
    }

    
    private function migrationFromConfig(string $path, callable $callback): void
    {
        $directory = new Directory($path);
        $iterator = $directory->getChildren();

        foreach ($iterator as $children) {
            if ($children->isFile()) {
                $config = (array)@include $children->getPath();
                call_user_func($callback, $config);
            }
        }
    }

    private function dropTables(): void
    {
        $connection = Application::getConnection();
        foreach ($this->orm as $orm) {
            $tableName = $orm::getTableName();
            if ($connection->isTableExists($tableName)) {
                $connection->dropTable($tableName);
            }
        }
    }

    private function getConfigPath(string $name): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . $name;
    }

    private function checkPhpVersion(): void
    {
        if (version_compare(phpversion(), self::PHP_VERSION) == -1) {
            throw new Exception(
                Loc::getMessage("SHOLOKHOV_EXCHANGE_MODULE_INVALID_PHP", ['#VERSION#' => self::PHP_VERSION])
            );
        }
    }

    private function checkComposer(): void
    {
        $autoload = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

        if (!file_exists($autoload)) {
            throw new Exception(
                Loc::getMessage('SHOLOKHOV_EXCHANGE_MODULE_INVALID_COMPOSER')
            );
        }
    }

    private function registrationEvents(): void
    {
        $eventManager = EventManager::getInstance();
        $eventManager->registerEventHandlerCompatible("main", "OnBeforeProlog", $this->MODULE_ID);
    }

    private function unRegistrationEvents(): void
    {
        $eventManager = EventManager::getInstance();
        $eventManager->unRegisterEventHandler("main", "OnBeforeProlog", $this->MODULE_ID);
    }
}
