<?php

namespace Sholokhov\Exchange\Target\Import\IBlock\Catalog;

use CPrice;

use Sholokhov\Exchange\Events\EventInterface;
use Sholokhov\Exchange\Events\ExchangeEvent;
use Sholokhov\Exchange\Fields\Catalog\CatalogFieldInterface;
use Sholokhov\Exchange\Fields\Catalog\PriceFieldInterface;
use Sholokhov\Exchange\Messages\DataResultInterface;
use Sholokhov\Exchange\Target\Attributes\Event as AttributesEvent;
use Sholokhov\Exchange\Target\Import\IBlock\Element;

use Bitrix\Catalog\ProductTable;
use Bitrix\Main\SystemException;
use Bitrix\Main\ObjectPropertyException;

/**
 * Импорт товаров в простой каталог
 * @todo Еще в разработке. Использовать можно, но много, что еще требуется реализовать
 *
 * @package Import
 */
class SimpleProduct extends Element
{
    /**
     * Установка цены продукта после импорта элемента
     *
     * @param EventInterface $event
     * @return void
     */
    #[AttributesEvent(ExchangeEvent::AfterImportItem)]
    private function setPrice(EventInterface $event): void
    {
        $result = $event->getParameter('result');
        $item = $event->getParameter('item');

        if (
            !($result instanceof DataResultInterface)
            || !$result->isSuccess()
            || !($id = $result->getData())
            || !is_array($item)

        ) {
            return;
        }

        $map = $this->getMappingRegistry()->getFields();

        foreach ($map as $field) {
            if ($field instanceof PriceFieldInterface) {
                $price = (float)($item[$field->getTo()] ?? 0);
                CPrice::SetBasePrice($id, $price, $field->getCurrency());
                break;
            }
        }
    }

    /**
     * Установка общего остатка продукта
     *
     * @param EventInterface $event
     * @return void
     * @throws ObjectPropertyException
     * @throws SystemException
     * @todo Установку остатков реализовать как отдельный обмен
     */
    #[AttributesEvent(ExchangeEvent::AfterImportItem)]
    private function setQuantity(EventInterface $event): void
    {
        $result = $event->getParameter('result');
        $item = $event->getParameter('item');

        if (
            !($result instanceof DataResultInterface)
            || !$result->isSuccess()
            || !($id = $result->getData())
            || !is_array($item)

        ) {
            return;
        }

        $map = $this->getMappingRegistry()->getFields();
        foreach ($map as $field) {
            if ($field instanceof CatalogFieldInterface && $field->isQuantity()) {
                if (ProductTable::getCount(['ID' => $id])) {
                    ProductTable::update($id, ['QUANTITY' => $item[$field->getTo()]]);
                } else {
                    ProductTable::add(['ID' => $id, 'QUANTITY' => $item[$field->getTo()]]);
                }
                break;
            }
        }
    }
}
