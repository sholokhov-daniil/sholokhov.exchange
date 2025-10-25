<?php

namespace Sholokhov\Exchange\Target\Import\IBlock;

use Sholokhov\Exchange\Repository\IBlock\IBlockRepository;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Структура обмена инфоблока
 *
 * @package Import
 */
interface IBlockExchangeInterface
{
    /**
     * Информационный блок в который идет импорт
     *
     * @return int
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getIBlockID(): int;

    /**
     * Получение информации об информационном блоке
     *
     * @return IBlockRepository|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getIBlockInfo(): ?IBlockRepository;
}