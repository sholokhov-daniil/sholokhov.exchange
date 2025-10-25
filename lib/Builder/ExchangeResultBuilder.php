<?php

namespace Sholokhov\Exchange\Builder;

use Exception;

use Sholokhov\Exchange\ExchangeInterface;
use Sholokhov\Exchange\Messages\ExchangeResultInterface;
use Sholokhov\Exchange\Messages\Type\ExchangeResult;
use Sholokhov\Exchange\Repository\Result\ResultRepositoryInterface;

class ExchangeResultBuilder
{
    /**
     * Создание объекта результата обмена
     *
     * @param ExchangeInterface $exchange
     * @param callable|null $repositoryFactory
     * @return ExchangeResultInterface
     * @throws Exception
     */
    public static function create(ExchangeInterface $exchange, callable $repositoryFactory = null): ExchangeResultInterface
    {
        $repository = $repositoryFactory ? self::createRepository($exchange, $repositoryFactory) : null;
        return new ExchangeResult($repository);
    }

    /**
     * Создание хранилище
     *
     * @param ExchangeInterface $exchange
     * @param callable $factory
     * @return ResultRepositoryInterface
     * @throws Exception
     */
    public static function createRepository(ExchangeInterface $exchange, callable $factory): ResultRepositoryInterface
    {
        $repository = call_user_func($factory, $exchange);

        if ($repository instanceof ResultRepositoryInterface) {
            return $repository;
        }

        throw new Exception('Result repository not implemented: ' . ResultRepositoryInterface::class);
    }
}