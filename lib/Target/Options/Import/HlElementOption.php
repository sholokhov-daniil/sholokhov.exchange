<?php

namespace Sholokhov\Exchange\Target\Options\Import;

use Sholokhov\Exchange\Target\Options\BaseOption;

/**
 * Конфигурация импорта элементов справочника
 */
class HlElementOption extends BaseOption
{
    /**
     * Идентификатор сущности в которую производится импорт
     *
     * @var int
     */
    public int $entityId;

    public function __construct(int $entityId)
    {
        $this->entityId = $entityId;
    }
}