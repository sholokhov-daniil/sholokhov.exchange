<?php

namespace Sholokhov\Exchange\Providers;

use Exception;
use Sholokhov\Exchange\Fields;
use Sholokhov\Exchange\Facade\FieldFacade;

/**
 * Регистрация карты обмена
 *
 * @internal
 */
class FieldServiceProvider
{
    /**
     * @return void
     * @throws Exception
     */
    public function __invoke(): void
    {
        FieldFacade::bind('base', Fields\Field::class);
        FieldFacade::bind('iblock.element', Fields\IBlock\IBlockElementField::class);
        FieldFacade::bind('xml.export', Fields\IBlock\IBlockElementField::class);
        FieldFacade::bind('catalog', Fields\IBlock\IBlockElementField::class);
        FieldFacade::bind('catalog.price', Fields\IBlock\IBlockElementField::class);

        // TODO: Добавить событие, для кастомных
    }
}