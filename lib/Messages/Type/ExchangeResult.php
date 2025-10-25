<?php

namespace Sholokhov\Exchange\Messages\Type;

use Sholokhov\Exchange\Messages\ExchangeResultInterface;
use Sholokhov\Exchange\Repository\Result\ResultRepositoryInterface;

/**
 * Результат выполненных действий
 *
 * @package Message
 */
class ExchangeResult extends Result implements ExchangeResultInterface
{
    public function __construct(private readonly ?ResultRepositoryInterface $repository = null)
    {
    }

    /**
     * Получение хранилища результата работы действия
     *
     * @return ResultRepositoryInterface|null
     */
    public function getData(): ?ResultRepositoryInterface
    {
        return $this->repository;
    }
}