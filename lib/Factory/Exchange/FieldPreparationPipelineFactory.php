<?php

namespace Sholokhov\Exchange\Factory\Exchange;

use Sholokhov\Exchange\Helper\Config;
use Sholokhov\Exchange\Helper\Helper;
use Sholokhov\Exchange\ExchangeInterface;
use Sholokhov\Exchange\Normalizers\NormalizerInterface;
use Sholokhov\Exchange\Preparation\FieldPreparationPipelineInterface;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

/**
 * @internal
 */
class FieldPreparationPipelineFactory
{
    /**
     * Создание процесса обмена
     *
     * @param ExchangeInterface $exchange
     * @return FieldPreparationPipelineInterface
     */
    public static function create(ExchangeInterface $exchange): FieldPreparationPipelineInterface
    {
        return self::resolve($exchange) ?: self::makeByExchange($exchange);
    }

    /**
     * Получение пользовательского процесса через событие
     *
     * @param ExchangeInterface $exchange
     * @return FieldPreparationPipelineInterface|null
     */
    private static function resolve(ExchangeInterface $exchange): ?FieldPreparationPipelineInterface
    {
        $event = new Event(Helper::getModuleID(), 'onBeforeCreatePreparationPipeline', compact('exchange'));
        $event->send();

        foreach ($event->getResults() as $result) {
            if ($result->getType() !== EventResult::SUCCESS) {
                continue;
            }

            $parameters = (array)$result->getParameters();
            $entity = $parameters['entity'] ?? null;

            if ($entity instanceof FieldPreparationPipelineInterface) {
                return $entity;
            }
        }

        return null;
    }

    /**
     * Создание преобразователя на основе обмена
     *
     * Если у обмена отсутствует собственный преобразователь,
     * то вернется преобразователь по умолчанию
     *
     * @param ExchangeInterface $exchange
     * @return FieldPreparationPipelineInterface
     */
    private static function makeByExchange(ExchangeInterface $exchange): FieldPreparationPipelineInterface
    {
        $normalizer = ValueNormalizerFactory::create($exchange);
        $entity = Config::get('target')['preparation'][$exchange::class] ?? null;

        if (is_subclass_of($entity, FieldPreparationPipelineInterface::class)) {
            return new $entity($normalizer);
        }

        return self::makeDefault($normalizer);
    }

    /**
     * Создание преобразователя по умолчанию
     *
     * @param NormalizerInterface $normalizer
     * @return FieldPreparationPipelineInterface
     */
    private static function makeDefault(NormalizerInterface $normalizer): FieldPreparationPipelineInterface
    {
        $entity = Config::get('target')['preparation']['default'];
        return new $entity($normalizer);
    }
}