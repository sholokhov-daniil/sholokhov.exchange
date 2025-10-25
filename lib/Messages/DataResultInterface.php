<?php

namespace Sholokhov\Exchange\Messages;

/**
 * @package Message
 */
interface DataResultInterface extends ResultInterface
{
    /**
     * Установка значения результата
     *
     * @param mixed $data
     * @return self
     */
    public function setData(mixed $data): self;

    /**
     * Получение данных результата действия
     *
     * @return mixed
     */
    public function getData(): mixed;
}