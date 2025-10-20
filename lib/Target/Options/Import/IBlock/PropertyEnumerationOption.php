<?php

namespace Sholokhov\Exchange\Target\Options\Import\IBlock;

/**
 * Конфигурация импорта элементов справочника
 */
class PropertyEnumerationOption extends IBlockOption
{
    /**
     * Код свойства, в которое производится импорт данных
     *
     * @var string
     */
    public string $propertyCode;

    public function __construct(int $iBLockId, string $propertyCode)
    {
        $this->propertyCode = $propertyCode;
        parent::__construct($iBLockId);
    }
}