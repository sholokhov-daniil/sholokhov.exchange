<?php

namespace Sholokhov\Exchange\Factory\Exchange;

use Exception;
use Sholokhov\Exchange\ExchangeInterface;
use Sholokhov\Exchange\Helper\Helper;
use Sholokhov\Exchange\ImportInterface;
use Sholokhov\Exchange\Processor\ImportProcessor;
use Sholokhov\Exchange\Processor\ProcessorInterface;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

/**
 * @internal
 */
class ProcessorFactory
{
    /**
     * Создание процесса обмена
     *
     * @param ExchangeInterface $exchange
     * @return ProcessorInterface
     * @throws Exception
     */
    public static function create(ExchangeInterface $exchange): ProcessorInterface
    {
        $processor = self::resolve($exchange);

        if ($processor) {
            return $processor;
        }

        if ($exchange instanceof ImportInterface) {
            return new ImportProcessor($exchange);
        }

        throw new Exception('Processor not found');
    }

    /**
     * Получение пользовательского процесса через событие
     *
     * @param ExchangeInterface $exchange
     * @return ProcessorInterface|null
     */
    private static function resolve(ExchangeInterface $exchange): ?ProcessorInterface
    {
        $event = new Event(Helper::getModuleID(), 'onBeforeCreateProcessor', compact('exchange'));
        $event->send();

        foreach ($event->getResults() as $result) {
            if ($result->getType() !== EventResult::SUCCESS) {
                continue;
            }

            $parameters = (array)$result->getParameters();
            $processor = $parameters['processor'] ?? null;

            if ($processor instanceof ProcessorInterface) {
                return $processor;
            }
        }

        return null;
    }
}