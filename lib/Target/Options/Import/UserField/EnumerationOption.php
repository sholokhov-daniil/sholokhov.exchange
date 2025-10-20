<?php

namespace Sholokhov\Exchange\Target\Options\Import\UserField;

/**
 * Конфигурация импорта файлов
 */
class EnumerationOption
{
    /**
     * ID сущности, которое принадлежит свойство
     *
     * @var string
     */
    public string $entityId;

    /**
     * Код свойства, в который производится импорт
     *
     * @var string
     */
    public string $propertyCode;

    public function __construct(string $entityId, string $propertyCode)
    {
        $this->entityId = $entityId;
        $this->propertyCode = $propertyCode;
    }
}