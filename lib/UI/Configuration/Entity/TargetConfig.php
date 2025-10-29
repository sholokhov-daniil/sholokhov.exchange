<?php

namespace Sholokhov\Exchange\UI\Configuration\Entity;

use Sholokhov\Exchange\UI\Configuration\Facade\FieldsRepository;

/**
 * Конфигурация UI обмена
 */
class TargetConfig extends EntityConfig
{
    /**
     * Доступный набор свойств
     *
     * @var EntityConfigInterface[]
     */
    protected array $fields;

    /**
     * Конфигурация отображения данных свойства
     *
     * @var array
     */
    protected array $fieldOptions;

    /**
     * Доступный набор свойств
     *
     * @return EntityConfigInterface[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Конфигурация отображения свойства
     *
     * @return array
     */
    public function getFieldOptions(): array
    {
        return $this->fieldOptions;
    }

    /**
     * Конфигурация объекта
     *
     * @param array $config
     * @return void
     */
    protected function configuration(array $config): void
    {
        parent::configuration($config);

        $this->fields = $this->searchFields($config['fields'] ?? []);
        $this->fieldOptions = $config['field_options'] ?? [];
    }

    /**
     * Поиск доступных свойств
     *
     * @param array $ids
     * @return array
     */
    protected function searchFields(array $ids): array
    {
        $result = [];

        foreach ($ids as $code) {
            if (FieldsRepository::has($code)) {
                $result[$code] = FieldsRepository::get($code);
            }
        }

        return $result;
    }
}