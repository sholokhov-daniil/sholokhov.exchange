<?php

namespace Sholokhov\Exchange\Factory\Exchange;

use Sholokhov\Exchange\Helper\Helper;
use Sholokhov\Exchange\ExchangeInterface;
use Sholokhov\Exchange\Normalizers\ValueNormalizer;
use Sholokhov\Exchange\Normalizers\NormalizerInterface;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

/**
 * @internal
 */
class ValueNormalizerFactory
{
    /**
     * Создание процесса обмена
     *
     * @param ExchangeInterface $exchange
     * @return NormalizerInterface
     */
    public static function create(ExchangeInterface $exchange): NormalizerInterface
    {
        return self::resolve($exchange) ?: new ValueNormalizer($exchange);
    }

    /**
     * Получение пользовательского процесса через событие
     *
     * @param ExchangeInterface $exchange
     * @return NormalizerInterface|null
     */
    private static function resolve(ExchangeInterface $exchange): ?NormalizerInterface
    {
        $event = new Event(Helper::getModuleID(), 'onBeforeCreatePreparationPipeline', compact('exchange'));
        $event->send();

        foreach ($event->getResults() as $result) {
            if ($result->getType() !== EventResult::SUCCESS) {
                continue;
            }

            $parameters = (array)$result->getParameters();
            $entity = $parameters['entity'] ?? null;

            if ($entity instanceof NormalizerInterface) {
                return $entity;
            }
        }

        return null;
    }
}