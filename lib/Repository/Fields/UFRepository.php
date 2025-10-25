<?php

namespace Sholokhov\Exchange\Repository\Fields;

use CUserTypeEntity;
use InvalidArgumentException;

/**
 * Хранилище информации о пользовательских свойствах определенной сущности
 *
 * Параметры управления конфигурацией:
 * | Параметр | Тип | Обязательный | Описание |
 * |------------------------------------------|
 * | entity_id | string | Да | ID сущности на основе которой производится поиск полей |
 *
 * @package Repository
 */
class UFRepository extends AbstractFieldRepository
{
    /**
     * Получение ID сущности, которому принадлежат поля
     *
     * @final
     * @return string
     */
    final public function getEntityId(): string
    {
        return $this->getOptions()->get('entity_id');
    }

    /**
     * Проверка конфигурации
     *
     * @param array $options
     * @return void
     */
    protected function checkOptions(array $options): void
    {
        if (!is_string($options['entity_id']) || !mb_strlen($options['entity_id'])) {
            throw new InvalidArgumentException('Option "entity_id" should be a string');
        }
    }

    /**
     * Запрос на получение доступных свойств
     *
     * @final
     * @param array $parameters
     * @return array
     */
    final protected function query(array $parameters = []): array
    {
        $fields = [];
        $filter = ['ENTITY_ID' => $this->getEntityId()];

        if (is_array($parameters['filter'])) {
            $filter = array_merge($parameters['filter'], $filter);
        }

        $iterator = CUserTypeEntity::GetList([], $filter);

        while ($item = $iterator->Fetch()) {
            $fields[$item['FIELD_NAME']] = $item;
        }

        return $fields;
    }

    /**
     * Поиск свойства по символьному коду свойства
     *
     * @final
     * @param string $id
     * @return mixed
     */
    final protected function search(string $id): mixed
    {
        $iterator = $this->query([
            'filter' => ['FIELD_NAME' => $id]
        ]);

        return $iterator[$id] ?? null;
    }

    /**
     * Получение идентификатора хранилища
     *
     * @return string
     */
    protected function generateId(): string
    {
        return static::class . '_' . $this->getEntityId();
    }
}