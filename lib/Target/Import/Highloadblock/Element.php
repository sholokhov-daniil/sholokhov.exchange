<?php

namespace Sholokhov\Exchange\Target\Import\Highloadblock;

use Exception;

use Sholokhov\Exchange\AbstractImport;
use Sholokhov\Exchange\Dispatcher\ExternalEventTypes;
use Sholokhov\Exchange\ExchangeMapTrait;
use Sholokhov\Exchange\Fields\FieldInterface;
use Sholokhov\Exchange\MappingExchangeInterface;
use Sholokhov\Exchange\Messages\DataResultInterface;
use Sholokhov\Exchange\Messages\Type\DataResult;
use Sholokhov\Exchange\Preparation\UserField as Prepare;
use Sholokhov\Exchange\Messages\Type\Error;
use Sholokhov\Exchange\Repository\Fields\UFRepository;

use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Highloadblock\HighloadBlockTable as HLT;

use Sholokhov\Exchange\Target\Options\Import\HlElementOption;

/**
 * Импорт элементов справочника
 *
 * @package Import
 */
class Element extends AbstractImport implements MappingExchangeInterface
{
    use ExchangeMapTrait;

    protected readonly DataManager|string $provider;

    /**
     * Конфигурация импорта
     *
     * @var HlElementOption
     */
    protected HlElementOption $options;

    /**
     * Хранилище пользовательских свойств
     *
     * @var UFRepository
     */
    private UFRepository $propertyRepository;

    /**
     * @param HlElementOption $options Конфигурация импорта
     * @throws Exception
     */
    public function __construct(HlElementOption $options)
    {
        $this->options = $options;
        parent::__construct();
    }

    /**
     * Свойство является множественным
     *
     * @inheritDoc
     * @param FieldInterface $field
     * @return bool
     * @throws LoaderException
     */
    public function isMultipleField(FieldInterface $field): bool
    {
        $property = $this->getPropertyRepository()->get($field->getTo());
        return $property && $property['MULTIPLE'] === 'Y';
    }

    /**
     * Получение ID справочника
     *
     * @return int
     */
    public function getHlID(): int
    {
        return $this->options->entityId;
    }

    /**
     * Конфигурация обмена
     *
     * @inheritDoc
     * @return void
     * @throws LoaderException
     */
    protected function configuration(): void
    {
        parent::configuration();
        $this->initPrepares();
    }

    /**
     * Получение доступных внешних событий обмена
     *
     * @inheritDoc
     * @return ExternalEventTypes
     */
    protected function getEventTypes(): ExternalEventTypes
    {
        $types = new ExternalEventTypes;
        $types->beforeUpdate = 'onBeforeHighloadblockElementUpdate';
        $types->afterUpdate = 'onAfterHighloadblockElementUpdate';
        $types->beforeAdd = 'onBeforeHighloadblockElementAdd';
        $types->afterAdd = 'onAfterHighloadblockElementAdd';

        return $types;
    }

    /**
     * Получение ID элемента из кэша
     *
     * @inheritDoc
     * @param array $item
     * @return int
     */
    protected function resolveId(array $item): int
    {
        $key = $this->getPrimaryField()->getTo();
        return (int)$this->getCache()->get($item[$key]);
    }

    /**
     * Проверка наличия элемента
     *
     * @inheritDoc
     * @param array $item
     * @return bool
     * @throws ArgumentException
     * @throws LoaderException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected function doExist(array $item): bool
    {
        $keyField = $this->getPrimaryField();

        // TODO: Добавить хэш импорта
        $filter = [
            $keyField->getTo() => $item[$keyField->getTo()],
        ];

        $select = [$keyField->getTo(), 'ID'];
        $element = $this->getDataProvider()::getRow(compact('filter', 'select'));

        if ($element) {
            $this->getCache()->set($item[$keyField->getTo()], (int)$element['ID']);
            return true;
        }

        return false;
    }

    /**
     * Создание элемента справочника
     *
     * @inheritDoc
     * @param array $fields
     * @param array $originalFields
     * @return DataResultInterface
     * @throws LoaderException
     * @throws SystemException
     * @throws Exception
     */
    protected function doAdd(array $fields, array $originalFields): DataResultInterface
    {
        $result = new DataResult;

        $addResult = $this->getDataProvider()::add($fields);

        if (!$addResult->isSuccess()) {
            $errorMessage = sprintf(
                'An error occurred when adding an element to the highloadblock "%s": %s',
                $this->getHlID(),
                implode('. ', $addResult->getErrorMessages())
            );
            $this->logger?->critical($errorMessage);

            return $result->addError(new Error($errorMessage, 500));
        }

        $result->setData($addResult->getId());
        $this->logger?->debug(sprintf('An element with the identifier "%s" has been added to the "%s" highloadblock', $addResult->getId(), $this->getHlID()));
        $this->getCache()->set($fields[$this->getPrimaryField()->getTo()], $addResult->getId());

        return $result;
    }

    /**
     * Обновление элемента справочника
     *
     * @inheritDoc
     * @param int $id
     * @param array $fields
     * @param array $originalFields
     * @return DataResultInterface
     * @throws LoaderException
     * @throws SystemException
     * @throws Exception
     */
    protected function doUpdate(int $id, array $fields, array $originalFields): DataResultInterface
    {
        $result = new DataResult;

        $updateResult = $this->getDataProvider()::update($id, $fields);

        if (!$updateResult->isSuccess()) {
            $errorMessage = sprintf(
                'An error occurred when updating an element with the ID "%s" to the highloadblock "%s": %s',
                $id,
                $this->getHlID(),
                implode('. ', $updateResult->getErrorMessages())
            );
            $this->logger?->critical($errorMessage);

            return $result->addError(new Error($errorMessage, 500, ['id' => $id]));
        }

        $this->logger?->debug(
            sprintf(
                'The element with the ID "%s" in the highloadblock "%s" has been successfully updated.',
                $id,
                $this->getHlID()
            )
        );

        return $result;
    }

    /**
     * Получение хранилища пользовательских свойств (UF)
     *
     * @return UFRepository
     * @throws LoaderException
     */
    protected function getPropertyRepository(): UFRepository
    {
        if (!isset($this->propertyRepository)) {
            if (!Loader::includeModule('highloadblock')) {
                throw new LoaderException('Module "highloadblock" not loaded.');
            }

            $entityId = HLT::compileEntityId($this->getHlID());

            $this->propertyRepository = new UFRepository(['entity_id' => $entityId]);
        }

        return $this->propertyRepository;
    }

    /**
     * Преобразование значений, для записи в справочник
     *
     * @param array $item
     * @return array
     */
    protected function prepareForAdd(array $item): array
    {
        return $item;
    }

    /**
     * Преобразование значения, для обновления справочника
     *
     * @param array $item
     * @return array
     */
    protected function prepareForUpdate(array $item): array
    {
        return $item;
    }

    /**
     * Инициализация провайдера данных
     *
     * @return string|DataManager
     * @throws LoaderException
     * @throws SystemException
     */
    protected function getDataProvider(): string|DataManager
    {
        if (isset($this->provider)) {
            return $this->provider;
        }

        $entity = HLT::compileEntity($this->getHlID());

        if (!$entity) {
            throw new LoaderException('Entity "' . $this->getHlID() . '" not found');
        }

        return $this->provider = $entity->getDataClass();
    }

    /**
     * Инициализация преобразователей импортированных значений
     *
     * @return void
     * @throws LoaderException
     */
    private function initPrepares(): void
    {
        $entityId = $this->getPropertyRepository()->getEntityId();

        $this->processor
            ->addPrepared(new Prepare\File($entityId))
            ->addPrepared(new Prepare\Date($entityId))
            ->addPrepared(new Prepare\DateTime($entityId))
            ->addPrepared(new Prepare\Boolean($entityId))
            ->addPrepared(new Prepare\IBlockElement($entityId))
            ->addPrepared(new Prepare\IBlockSection($entityId))
            ->addPrepared(new Prepare\Enumeration($entityId));

        // Адрес
        // Видео
        // Деньги
        // Опрос
        // Привязка к элементам справочника
        // Содержимое ссылки
        // Ссылка
        // Целое число
        // Число
        // Шаблон
    }
}