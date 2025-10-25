<?php

namespace Sholokhov\Exchange\Fields\Catalog;

use Sholokhov\Exchange\Fields\Field;

/**
 * Описание свойства каталога
 *
 * @package Field
 */
class CatalogField extends Field implements CatalogFieldInterface
{
    /**
     * Свойство хранит цену
     *
     * @return bool
     */
    public function isQuantity(): bool
    {
        return $this->getContainer()->get('quantity', false);
    }

    /**
     * Свойство хранит цену
     *
     * @param bool $quantity
     * @return $this
     */
    public function setQuantity(bool $quantity = true): self
    {
        $this->getContainer()->set('quantity', $quantity);
        return $this;
    }
}
