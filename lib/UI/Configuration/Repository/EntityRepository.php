<?php

namespace Sholokhov\Exchange\UI\Configuration\Repository;

use InvalidArgumentException;

use Sholokhov\Exchange\Helper\Config;
use Sholokhov\Exchange\UI\Configuration\Entity\EntityConfigInterface;

/**
 * Хранилище конфигураций сущностей
 */
class EntityRepository
{
    /**
     * Тип(группа) сущности
     *
     * @var string
     */
    protected string $type;

    /**
     * Сущность хранящая конфигурацию
     *
     * @var class-string<EntityConfigInterface>
     */
    protected string $entity;

    /**
     * Конфигурации сущностей
     *
     * @var EntityConfigInterface[]
     */
    private array $configuration;

    /**
     * @param string $type
     * @param class-string<EntityConfigInterface> $entity
     */
    public function __construct(string $type, string $entity)
    {
        if (!is_subclass_of($entity, EntityConfigInterface::class)) {
            throw new InvalidArgumentException('Entity class must be an instance of '. EntityConfigInterface::class);
        }

        $this->type = $type;
        $this->entity = $entity;

        $this->load();
    }

    /**
     * Получение конфигурации по ID
     *
     * @param string $id
     * @return EntityConfigInterface|null
     */
    public function get(string $id): EntityConfigInterface|null
    {
        return $this->configuration[$id] ?? null;
    }

    /**
     * Проверка наличия конфигурации
     *
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool
    {
        return isset($this->configuration[$id]);
    }

    /**
     * Получение доступных конфигураций
     *
     * @return EntityConfigInterface[]
     */
    public function getAll(): array
    {
        return $this->configuration;
    }

    /**
     * Инициализация конфигураций
     *
     * @return void
     */
    protected function load(): void
    {
        $result = [];
        $iterator = Config::get('ui')[$this->type] ?? [];

        foreach ($iterator as $data) {
            $config = $this->entity::fromArray($data);
            $result[$config->getEntity()] = $config;
        }

        $this->configuration = $result;
    }
}