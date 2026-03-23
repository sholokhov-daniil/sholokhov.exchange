<?php

namespace Sholokhov\Exchange\Provider\Entity;

use CIBlockResult;

interface IBlockProviderInterface extends EntityProviderInterface
{
    /**
     * Выполнить запрос на получение данных
     *
     * @return CIBlockResult|null
     */
    public function query(): ?CIBlockResult;
}