<?php

namespace Sholokhov\Exchange\Providers;


use Sholokhov\Exchange\Container\Container;
use Sholokhov\Exchange\Fields;
use Sholokhov\Exchange\Facade\FieldFacade;
use Sholokhov\Exchange\UI\Configuration\Entity\EntityConfig;
use Sholokhov\Exchange\UI\Configuration\Repository\EntityRepository;

use Bitrix\Main\ObjectNotFoundException;

use Psr\Container\NotFoundExceptionInterface;

/**
 * Регистрация карты обмена
 *
 * @internal
 */
class FieldServiceProvider
{
    /**
     * @return void
     * @throws ObjectNotFoundException
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(): void
    {
        FieldFacade::bind('base', Fields\Field::class);
        FieldFacade::bind('iblock.element', Fields\IBlock\IBlockElementField::class);
        FieldFacade::bind('xml.export', Fields\IBlock\IBlockElementField::class);
        FieldFacade::bind('catalog', Fields\IBlock\IBlockElementField::class);
        FieldFacade::bind('catalog.price', Fields\IBlock\IBlockElementField::class);

        Container::getInstance()->set('ui.fields.repository', new EntityRepository('fields', EntityConfig::class));

        // TODO: Добавить событие, для кастомных
    }
}