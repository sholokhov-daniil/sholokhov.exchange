<?php

namespace Sholokhov\Exchange\UI\Configuration\Entity;

use Bitrix\Main\Localization\Loc;

/**
 * Конфигурация UI сущности
 */
class EntityConfig implements EntityConfigInterface
{
    /**
     * Сущность, которой относятся настройка
     *
     * @var string
     */
    protected string $entity;

    /**
     * Наименование сущности
     *
     * @var string
     */
    protected string $name;

    /**
     * Подробное описание сущности
     *
     * @var string
     */
    protected string $description;

    /**
     * @param array $config Конфигурация сущности
     */
    public function __construct(array $config)
    {
        $this->configuration($config);
    }

    /**
     * Создание объекта настроек на основе конфигурационного массива
     *
     * @param array $config
     * @return static
     */
    public static function fromArray(array $config): static
    {
        return new static($config);
    }

    /**
     * Сущность, которой относятся настройка
     *
     * @return string
     */
    public function getEntity(): string
    {
        return $this->entity;
    }

    /**
     * Наименование сущности
     *
     * @return string
     */
    public function getName(): string
    {
        if ($this->name === '') {
            return '';
        }

        return Loc::getMessage($this->name) ?: '';
    }

    /**
     * Подробное описание сущности
     *
     * @return string
     */
    public function getDescription(): string
    {
        if ($this->description === '') {
            return '';
        }

        return Loc::getMessage($this->description) ?: '';
    }

    /**
     * Конфигурация объекта
     *
     * @param array $config
     * @return void
     */
    protected function configuration(array $config): void
    {
        $this->entity = (string)($config['entity'] ?? '');
        $this->name = (string)($config['name'] ?? '');
        $this->description = (string)($config['description'] ?? '');
    }
}