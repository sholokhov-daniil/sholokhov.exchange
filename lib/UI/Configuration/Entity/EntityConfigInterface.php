<?php

namespace Sholokhov\Exchange\UI\Configuration\Entity;

interface EntityConfigInterface
{
    /**
     * Сущность, которой относятся настройка
     *
     * @return string
     */
    public function getEntity(): string;

    /**
     * Название сущности в настройках
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Подробное описание сущности
     *
     * @return string
     */
    public function getDescription(): string;

    /**
     * Создание объекта настроек на основе конфигурационного массива
     *
     * @param array $config
     * @return static
     */
    public static function fromArray(array $config): static;
}
