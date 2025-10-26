<?php

namespace Sholokhov\Exchange\Providers;

use Sholokhov\Exchange\Target;
use Sholokhov\Exchange\Facade\TargetContainer;

/**
 * Регистрация обменов
 *
 * @internal
 */
class TargetServiceProvider
{
    public function __invoke(): void
    {
        TargetContainer::bind('import.file', Target\Import\File::class);
        TargetContainer::bind('import.uf.enum', Target\Import\UserFields\Enumeration::class);
        TargetContainer::bind('import.warehouse', Target\Import\Sale\Warehouse::class);
        TargetContainer::bind('import.iblock.section', Target\Import\IBlock\Section::class);
        TargetContainer::bind('import.iblock.element', Target\Import\IBlock\Element::class);
        TargetContainer::bind('import.iblock.props.enum', Target\Import\IBlock\Property\PropertyEnumeration::class);
        TargetContainer::bind('import.catalog.product.simple', Target\Import\IBlock\Catalog\SimpleProduct::class);
        TargetContainer::bind('import.hl.element', Target\Import\Highloadblock\Element::class);

        // TODO: Добавить событие, для кастомных
    }
}