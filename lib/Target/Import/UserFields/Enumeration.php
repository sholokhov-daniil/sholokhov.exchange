<?php

namespace Sholokhov\Exchange\Target\Import\UserFields;

use CUserFieldEnum;

use Sholokhov\Exchange\AbstractImport;
use Sholokhov\Exchange\Dispatcher\ExternalEventTypes;
use Sholokhov\Exchange\ExchangeMapTrait;
use Sholokhov\Exchange\Fields\FieldInterface;
use Sholokhov\Exchange\MappingExchangeInterface;
use Sholokhov\Exchange\Messages\DataResultInterface;
use Sholokhov\Exchange\Messages\Type\DataResult;
use Sholokhov\Exchange\Repository\Fields\UFRepository;
use Sholokhov\Exchange\Messages\Type\Error;
use Sholokhov\Exchange\Target\Options\Import\UserField\EnumerationOption;

/**
 * Импорт значений списка
 *
 * @package Import
 */
class Enumeration extends AbstractImport implements MappingExchangeInterface, ExchangeUserFieldInterface
{
    use ExchangeMapTrait;

    /**
     * Хранилище UF свойств
     *
     * @var UFRepository
     */
    protected readonly UfRepository $propertyRepository;

    /**
     * Конфигурация импорта
     *
     * @var EnumerationOption
     */
    protected readonly EnumerationOption $option;

    public function __construct(EnumerationOption $options)
    {
        $this->option = $options;
        parent::__construct();
    }

    /**
     * Поле в которое производится импорт является множественным
     *
     * @param FieldInterface $field
     * @return bool
     */
    public function isMultipleField(FieldInterface $field): bool
    {
        return $this->getProperty()['MULTIPLE'] === 'Y';
    }

    /**
     * Получение идентификатора сущности которой относится пользовательское свойство(UF)
     *
     * @return string
     */
    public function getEntityId(): string
    {
        return $this->option->entityId;
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
     * Получение доступных внешних событий обмена
     *
     * @inheritDoc
     * @return ExternalEventTypes
     */
    protected function getEventTypes(): ExternalEventTypes
    {
        $types = new ExternalEventTypes;
        $types->beforeUpdate = 'onBeforeUFEnumerationUpdate';
        $types->afterUpdate = 'onAfterUFEnumerationUpdate';
        $types->beforeAdd = 'onBeforeUFEnumerationAdd';
        $types->afterAdd = 'onAfterUFEnumerationAdd';

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
     * Проверка наличия значения списка
     *
     * @param array $item
     * @return bool
     */
    protected function doExist(array $item): bool
    {
        $primary = $this->getPrimaryField();

        if ($enum = $this->searchEnum($item[$primary->getTo()])) {
            $this->getCache()->set($item[$primary->getTo()], $enum['ID']);
            return true;
        }

        return false;
    }

    /**
     * Создание значения списка
     *
     * @param array $fields
     * @param array $originalFields
     * @return DataResultInterface
     */
    protected function doAdd(array $fields, array $originalFields): DataResultInterface
    {
        global $APPLICATION;

        $result = new DataResult;
        $uf = new CUserFieldEnum;

        $setResult = $uf->SetEnumValues(
            $this->getProperty()['ID'],
            [
                'n0' => $fields
            ]
        );

        if (!$setResult) {
            return $result->addError(
                new Error(
                    'An error occurred when creating the list value: ' . strip_tags($APPLICATION->GetException()),
                    500,
                    $fields
                )
            );
        }

        $primary = $this->getPrimaryField();
        $enum = $this->searchEnum($fields[$primary->getTo()]);
        $this->getCache()->set($originalFields[$primary->getTo()], (int)$enum['ID']);
        $result->setData((int)$enum['ID']);

        return $result;
    }

    /**
     * Обновление значения списка
     *
     * @param int $id
     * @param array $fields
     * @param array $originalFields
     * @return DataResultInterface
     */
    protected function doUpdate(int $id, array $fields, array $originalFields): DataResultInterface
    {
        $result = new DataResult;
        $primary = $this->getPrimaryField();

        $this->searchEnum($fields[$primary->getTo()]);

        $uf = new CUserFieldEnum;
        if (!$uf->SetEnumValues($this->getProperty()['ID'], [$id => $fields])) {
            return $result->addError(new Error('An error occurred when creating the list value', 500));
        }

        $this->logger?->debug(sprintf('Updated the value of the list with the ID "%s"', $id));

        return $result;
    }

    /**
     * Подготовка данных, для создания
     *
     * @param array $item
     * @return array
     */
    protected function prepareForAdd(array $item): array
    {
        return $this->prepare($item);
    }

    /**
     * Преобразование данных, для обновления
     *
     * @param array $item
     * @return array
     */
    protected function prepareForUpdate(array $item): array
    {
        return $this->prepare($item);
    }

    /**
     * Найти значение списка
     *
     * @param mixed $value
     * @return array
     */
    private function searchEnum(mixed $value): array
    {
        $primary = $this->getPrimaryField();
        $filter = [
            'USER_FIELD_ID' => $this->getProperty()['ID'],
            $primary->getTo() => $value,
        ];

        return CUserFieldEnum::GetList([], $filter)->Fetch() ?: [];
    }

    /**
     * Преобразование импортируемого значения
     *
     * @param array $item
     * @return array
     */
    private function prepare(array $item): array
    {
        $result = [];
        $supportedFields = $this->getSupportedFields();
        $map = $this->getMappingRegistry()->getFields();

        foreach ($map as $field) {
            if (in_array($field->getTo(), $supportedFields)) {
                $result[$field->getTo()] = $item[$field->getTo()];
            }
        }

        return $result;
    }

    /**
     * Получение хранилища импортированного поля
     *
     * @return array
     */
    private function getProperty(): array
    {
        return $this->getPropertyRepository()->get($this->getPropertyCode(), []);
    }

    /**
     * Получение хранилища информации о пользовательских свойствах (UF)
     *
     * @return UFRepository
     */
    private function getPropertyRepository(): UFRepository
    {
        return $this->propertyRepository ??= new UFRepository(['entity_id' => $this->getEntityID()]);
    }

    /**
     * Список поддерживаемых полей значения свойства, для импортирования
     *
     * @return string[]
     */
    private function getSupportedFields(): array
    {
        return ['VALUE', 'DEF', 'SORT', 'XML_ID'];
    }
}