<?php

namespace Sholokhov\Exchange\Fields\Catalog;

use Sholokhov\Exchange\Fields\FieldInterface;

/**
 * @package Field
 */
interface PriceFieldInterface extends FieldInterface
{
    /**
     * Валюта
     *
     * @return string
     */
    public function getCurrency(): string;
}