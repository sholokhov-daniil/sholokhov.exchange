<?php

namespace Sholokhov\Exchange\Target\Attributes;

use Attribute;
use Sholokhov\Exchange\Events\ExchangeEvent;

/**
 * Производит указание, что метод подписан на системное событие обмена
 *
 * @package Attribute
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Event
{

    /**
     * @param ExchangeEvent $type Тип события
     */
    public function __construct(private readonly ExchangeEvent $type)
    {
    }

    /**
     * Получение типа события
     *
     * @return ExchangeEvent
     */
    public function getType(): ExchangeEvent
    {
        return $this->type;
    }
}
