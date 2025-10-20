<?php

namespace Sholokhov\Exchange\Target\Import\IBlock;

use CIBlock;

use Sholokhov\Exchange\ExchangeInterface;
use Sholokhov\Exchange\Repository\IBlock\IBlockRepository;

use Psr\Container\NotFoundExceptionInterface;
use Psr\Container\ContainerExceptionInterface;
use Sholokhov\Exchange\Repository\IBlock\PropertyRepository;

/**
 * @package Import
 * @mixin ExchangeInterface
 * @implements IBlockExchangeInterface
 */
trait IBlockTrait
{
    /**
     * Хранилище информации инфоблока
     *
     * @var IBlockRepository|null
     */
    private ?IBlockRepository $iBlockRepository = null;

    /**
     * Получение информации об информационном блоке
     *
     * @return IBlockRepository|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getIBlockInfo(): ?IBlockRepository
    {
        return $this->iBlockRepository ??= new IBlockRepository($this->getIBlockID());
    }

    /**
     * Получение хранилища свойств инфоблока
     *
     * @return PropertyRepository
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getPropertyRepository(): PropertyRepository
    {
        return $this->propertyRepository ??= new PropertyRepository(['iblock_id' => $this->getIBlockID()]);
    }

    /**
     * Очистка кэша ИБ
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function cleanCache(): void
    {
        CIBlock::CleanCache($this->getIBlockID());
        CIBlock::clearIblockTagCache($this->getIBlockID());
    }
}