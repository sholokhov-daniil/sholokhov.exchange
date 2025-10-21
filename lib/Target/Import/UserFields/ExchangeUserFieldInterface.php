<?php

namespace Sholokhov\Exchange\Target\Import\UserFields;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Sholokhov\Exchange\ExchangeInterface;

interface ExchangeUserFieldInterface extends ExchangeInterface
{
    /**
     * Получение идентификатора сущности которой относится пользовательское свойство(UF)
     *
     * @return string
     */
    public function getEntityId(): string;

    /**
     * Получение кода свойства в которое производится импорт данных
     *
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getPropertyCode(): string;
}