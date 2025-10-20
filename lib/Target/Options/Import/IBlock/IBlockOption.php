<?php

namespace Sholokhov\Exchange\Target\Options\Import\IBlock;

use Sholokhov\Exchange\Target\Options\BaseOption;

/**
 * Конфигурация импорта в информационный блок
 */
class IBlockOption extends BaseOption
{
    /**
     * ID инфоблока в который производится импорт
     *
     * @var int
     */
    public int $iBlockId;

    public function __construct(int $iBlockId)
    {
        $this->iBlockId = $iBlockId;
    }
}