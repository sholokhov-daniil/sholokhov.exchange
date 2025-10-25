<?php

namespace Sholokhov\Exchange\Fields\Catalog;

use Sholokhov\Exchange\Fields\FieldInterface;

/**
 * Структура свойства каталога
 *
 * @package Field
 */
interface CatalogFieldInterface extends FieldInterface
{
    /**
     * Значение является ценой
     *
     * @return bool
     */
    public function isQuantity(): bool;
}
