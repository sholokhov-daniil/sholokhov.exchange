<?php

namespace Sholokhov\Exchange\Providers;

use Sholokhov\Exchange\Container\Container;
use Sholokhov\Exchange\Contracts\FieldContract;
use Sholokhov\Exchange\Contracts\SourceContract;
use Sholokhov\Exchange\Contracts\TargetContract;

use Bitrix\Main\ObjectNotFoundException;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Регистрация карты обмена
 *
 * @internal
 */
class ContractServiceProvider
{
    /**
     * @return void
     * @throws ObjectNotFoundException
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(): void
    {
        Container::getInstance()->set('fieldContract', new FieldContract);
        Container::getInstance()->set("sourceContract", new SourceContract);
        Container::getInstance()->set("targetContract", new TargetContract);
    }
}