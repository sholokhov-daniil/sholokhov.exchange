<?php

namespace Sholokhov\Exchange\Messages;

use Sholokhov\Exchange\Repository\Result\ResultRepositoryInterface;

/**
 * @package Message
 */
interface ExchangeResultInterface extends ResultInterface
{
    /**
     * Получение результата работы
     *
     * @return ResultRepositoryInterface|null
     */
    public function getData(): ?ResultRepositoryInterface;
}