<?php

namespace Sholokhov\Exchange\Bootstrap;

use Exception;
use Sholokhov\Exchange\Fields;
use Sholokhov\Exchange\Facade\FieldContainer;

/**
 * @internal
 */
class FieldBootstrap implements BootstrapInterface
{
    /**
     * @return void
     * @throws Exception
     */
    public function bootstrap(): void
    {
        FieldContainer
            ::bind('base', Fields\Field::class)
            ->bind('iBlockElement', Fields\IBlock\IBlockElementField::class)
            ->bind('exportXml', Fields\IBlock\IBlockElementField::class)
            ->bind('catalog', Fields\IBlock\IBlockElementField::class)
            ->bind('catalogPrice', Fields\IBlock\IBlockElementField::class);
    }
}