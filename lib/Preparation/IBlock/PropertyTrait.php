<?php

namespace Sholokhov\Exchange\Preparation\IBlock;

use Sholokhov\Exchange\Repository\IBlock\PropertyRepository;

/**
 * @package Preparation
 */
trait PropertyTrait
{
    /**
     * Хранилище информации о свойствах
     *
     * @var PropertyRepository
     */
    private PropertyRepository $repository;

    /**
     * ID информационного блока, для которого необходимо подгрузить информацию о свойствах
     *
     * @var int
     */
    protected readonly int $iblockId;

    /**
     * Получение хранилища
     *
     * @return PropertyRepository
     */
    final protected function getPropertyRepository(): PropertyRepository
    {
        return $this->repository ??= new PropertyRepository(['iblock_id' => $this->iblockId]);
    }
}