<?php

namespace Sholokhov\Exchange\Target\Import\Sale;

use Exception;

use Sholokhov\Exchange\AbstractImport;
use Sholokhov\Exchange\Dispatcher\ExternalEventTypes;
use Sholokhov\Exchange\ExchangeMapTrait;
use Sholokhov\Exchange\Fields\FieldInterface;
use Sholokhov\Exchange\MappingExchangeInterface;
use Sholokhov\Exchange\Messages\DataResultInterface;
use Sholokhov\Exchange\Messages\Type\DataResult;
use Sholokhov\Exchange\Messages\Type\Error;
use Sholokhov\Exchange\Repository\Fields\UFRepository;

use Bitrix\Catalog\StoreTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;

/**
 * Производит импорт складов
 *
 * @package Import
 */
class Warehouse extends AbstractImport implements MappingExchangeInterface
{
    use ExchangeMapTrait;

    /**
     * Хранилище пользовательских полей
     *
     * @var UFRepository
     */
    protected UFRepository $ufRepository;

    /**
     * Проверка множественности значения
     *
     * @param FieldInterface $field
     * @return bool
     */
    public function isMultipleField(FieldInterface $field): bool
    {
        $code = $field->getTo();

        if ($this->isUserField($code)) {
            $uf = $this->getUfRepository()->get($code);
            return $uf['MULTIPLE'] === 'Y';
        }

        return false;
    }

    /**
     * Тип доступных событий импорта
     *
     * @return ExternalEventTypes
     */
    protected function getEventTypes(): ExternalEventTypes
    {
        $types = new ExternalEventTypes;
        $types->beforeUpdate = 'onBeforeWarehouseUpdate';
        $types->afterUpdate = 'onAfterWarehouseUpdate';
        $types->beforeAdd = 'onBeforeWarehouseAdd';
        $types->afterAdd = 'onAfterWarehouseAdd';

        return $types;
    }

    /**
     * Получение ID склада из кэша
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
     * Логика проверки наличия склада
     *
     * @param array $item
     * @return bool
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected function doExist(array $item): bool
    {
        $fieldKey = $this->getPrimaryField()->getTo();
        $externalId = $item[$fieldKey];

        $select = ['ID'];
        $filter = [$fieldKey=> $externalId];
        $store = StoreTable::getRow(compact('filter', 'select'));

        if ($store) {
            $this->getCache()->set($externalId, (int)$store['ID']);
            return true;
        }

        return false;
    }

    /**
     * Создание нового склада
     *
     * @param array $fields
     * @param array $originalFields
     * @return DataResultInterface
     * @throws Exception
     */
    protected function doAdd(array $fields, array $originalFields): DataResultInterface
    {
        $result = new DataResult;

        $addResult = StoreTable::add($fields['FIELDS']);
        if (!$addResult->isSuccess()) {
            return $result->addError(
                new Error(
                    'An error occurred when creating the warehouse: ' . implode('.', $addResult->getErrorMessages()),
                    500
                )
            );
        }

        if (!$this->setUserFields($addResult->getId(), $fields['UF'])) {
            $result->addError(new Error('Error update user fields'));
        }

        $primary = $this->getPrimaryField();
        $this->getCache()->set($originalFields[$primary->getTo()], $addResult->getId());
        $result->setData($addResult->getId());

        return $result;
    }

    /**
     * Обновление склада
     *
     * @param int $id
     * @param array $fields
     * @param array $originalFields
     * @return DataResultInterface
     * @throws Exception
     */
    protected function doUpdate(int $id, array $fields, array $originalFields): DataResultInterface
    {
        $result = new DataResult;

        $updateResult = StoreTable::update($id, $fields['FIELDS']);
        if (!$updateResult->isSuccess()) {
            return $result->addError(
                new Error(
                    'An error occurred when updating warehouse: ' . implode('.', $updateResult->getErrorMessages()),
                    500
                )
            );
        }

        if (!$this->setUserFields($id, $fields['UF'])) {
            $result->addError(new Error('Error update user fields'));
        }

        $result->setData($id);

        return $result;
    }

    /**
     * Подготовка данных, для добавления нового склада
     *
     * @param array $item
     * @return array|array[]
     */
    protected function prepareForAdd(array $item): array
    {
        return $this->preparation($item);
    }

    /**
     * Подготовка данных, для обновления существующего склада
     *
     * @param array $item
     * @return array|array[]
     */
    protected function prepareForUpdate(array $item): array
    {
        return $this->preparation($item);
    }

    /**
     * Получение хранилища пользовательских свойств
     *
     * @return UFRepository
     */
    protected function getUfRepository(): UFRepository
    {
        return $this->ufRepository ??= new UFRepository(['entity_id' => 'CAT_STORE']);
    }

    /**
     * Установка значений UF у склада
     *
     * @param int $id
     * @param array $fields
     * @return bool
     * @author Daniil S.
     */
    private function setUserFields(int $id, array $fields): bool
    {
        global $USER_FIELD_MANAGER;
        return $USER_FIELD_MANAGER->Update($this->getUfRepository()->getId(), $id, $fields);
    }

    /**
     * Преобразование данных склада
     *
     * @param array $fields
     * @return array{UF: array, FIELDS: array}
     */
    private function preparation(array $fields): array
    {
        $result = [
            'UF' => [],
            'FIELDS' => []
        ];

        if (array_key_exists('ACTIVE', $fields) && !isset($fields['ACTIVE'])) {
            unset($fields['ACTIVE']);
        }

        foreach ($fields as $code => $value) {
            $group = $this->isUserField($code) ? 'UF' : 'FIELDS';
            $result[$group][$code] = $value;
        }

        return $result;
    }

    /**
     * Код свойства относится к пользовательским полям
     *
     * @param string $code
     * @return bool
     */
    private function isUserField(string $code): bool
    {
        return str_starts_with($code, 'UF_');
    }
}