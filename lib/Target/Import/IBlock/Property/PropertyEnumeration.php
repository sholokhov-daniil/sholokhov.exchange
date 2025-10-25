<?php

namespace Sholokhov\Exchange\Target\Import\IBlock\Property;

use CIBlockPropertyEnum;

use Sholokhov\Exchange\AbstractImport;
use Sholokhov\Exchange\Dispatcher\ExternalEventTypes;
use Sholokhov\Exchange\ExchangeMapTrait;
use Sholokhov\Exchange\MappingExchangeInterface;
use Sholokhov\Exchange\Target\Import\IBlock\IBlockExchangeInterface;
use Sholokhov\Exchange\Target\Import\IBlock\IBlockTrait;

use Sholokhov\Exchange\Fields\FieldInterface;
use Sholokhov\Exchange\Messages\DataResultInterface;
use Sholokhov\Exchange\Messages\Type\DataResult;
use Sholokhov\Exchange\Messages\Type\Error;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Sholokhov\Exchange\Target\Options\Import\IBlock\PropertyEnumerationOption;

/**
 * Импорт значений списка информационного блока
 *
 * @package Import
 */
class PropertyEnumeration extends AbstractImport implements MappingExchangeInterface, IBlockExchangeInterface
{
    use ExchangeMapTrait,
        IBlockTrait;

    protected readonly PropertyEnumerationOption $option;

    /**
     * ID инфоблока, в который производится импорт
     *
     * @return int
     */
    public function getIBlockID(): int
    {
        return $this->option->iBlockId;
    }

    /**
     * Получение информации о свойстве
     *
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getProperty(): array
    {
        return $this->getPropertyRepository()->get($this->getPropertyCode(), []);
    }

    /**
     * Получение кода свойства в которое производится импорт данных
     *
     * @return string
     */
    public function getPropertyCode(): string
    {
        return $this->option->propertyCode;
    }

    /**
     * Проверка на множественный тип свойства
     *
     * @inheritDoc
     * @param FieldInterface $field
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function isMultipleField(FieldInterface $field): bool
    {
        $repository = $this->getPropertyRepository()->get($field->getTo());
        return $repository && $repository['MULTIPLE'] === 'Y';
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
        $types->beforeUpdate = 'onBeforeIBlockPropertyEnumerationUpdate';
        $types->afterUpdate = 'onAfterIBlockPropertyEnumerationUpdate';
        $types->beforeAdd = 'onBeforeIBlockPropertyEnumerationAdd';
        $types->afterAdd = 'onAfterIBlockPropertyEnumerationAdd';

        return $types;
    }

    /**
     * Получение ID значение из кэша
     *
     * @param array $item
     * @return int
     */
    protected function resolveId(array $item): int
    {
        $key = $this->getPrimaryField()->getTo();
        return (int)$this->getCache()->get($item[$key]);
    }

    /**
     * Проверка наличия значения свойства
     *
     * @inheritDoc
     * @param array $item
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function doExist(array $item): bool
    {
        $primaryField = $this->getPrimaryField();
        $externalId = $item[$primaryField->getTo()];

        $filter = [
            'IBLOCK_ID' => $this->getIBlockID(),
            'PROPERTY_ID' => $this->getProperty()['ID'],
            $primaryField->getTo() => $externalId,
        ];

        if ($enum = CIBlockPropertyEnum::GetList([], $filter)->Fetch()) {
            $this->getCache()->set($externalId, (int)$enum['ID']);
            return true;
        }

        return false;
    }

    /**
     * Создание значения списка
     *
     * @inheritDoc
     * @param array $fields
     * @param array $originalFields
     * @return DataResultInterface
     */
    protected function doAdd(array $fields, array $originalFields): DataResultInterface
    {
        $result = new DataResult;

        if ($enumId = CIBlockPropertyEnum::Add($fields)) {
            $result->setData((int)$enumId);
            $this->logger?->debug(sprintf('Added the value of the list with the ID "%s"', $enumId));
            $this->getCache()->set($originalFields[$this->getPrimaryField()->getTo()], (int)$enumId);
        } else {
            $result->addError(new Error('An error occurred when creating the list value', 500));
        }

        return $result;
    }

    /**
     * Обновление значения списка
     *
     * @inheritDoc
     * @param int $id
     * @param array $fields
     * @param array $originalFields
     * @return DataResultInterface
     */
    protected function doUpdate(int $id, array $fields, array $originalFields): DataResultInterface
    {
        $result = new DataResult;

        if (!CIBlockPropertyEnum::Update($id, $fields)) {
            return $result->addError(new Error('An error occurred when creating the list value', 500, $fields));
        }

        $this->logger?->debug(sprintf('Updated the value of the list with the ID "%s"', $id));

        return $result;
    }

    /**
     * Преобразование данных, для создания
     *
     * @param array $item
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function prepareForAdd(array $item): array
    {
        return $this->prepare($item);
    }

    /**
     * Преобразование данных для обновления
     *
     * @param array $item
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function prepareForUpdate(array $item): array
    {
        return $this->prepare($item);
    }

    /**
     * Преобразование импортируемого значения
     *
     * @param array $item
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function prepare(array $item): array
    {
        $result = [];
        $supportedFields = $this->getSupportedFields();
        $iterator = $this->getMappingRegistry()->getFields();

        foreach ($iterator as $field) {
            if (in_array($field->getTo(), $supportedFields)) {
                $result[$field->getTo()] = $item[$field->getTo()];
            }
        }

        $result['PROPERTY_ID'] = $this->getProperty()['ID'];
        $result['IBLOCK_ID'] = $this->getIBlockID();

        return $result;
    }

    /**
     * Список поддерживаемых полей значения свойства, для импортирования
     *
     * @return string[]
     */
    private function getSupportedFields(): array
    {
        return ['VALUE', 'ID', 'SORT', 'DEF', 'XML_ID', 'EXTERNAL_ID'];
    }
}