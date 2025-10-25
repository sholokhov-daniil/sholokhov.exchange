<?php

namespace Sholokhov\Exchange\Messages\Type;

use Sholokhov\Exchange\Messages\DataResultInterface;

/**
 * Результата работы с произвольными данными
 *
 * @package Message
 */
class DataResult extends Result implements DataResultInterface
{
    /**
     * @var mixed|null
     */
    protected mixed $data = null;

    /**
     * Установка результата выполнения
     *
     * @param mixed $data
     * @return DataResultInterface
     */
    public function setData(mixed $data): DataResultInterface
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Получение дополнительной информации результата выполнения
     *
     * @return mixed
     */
    public function getData(): mixed
    {
        return $this->data;
    }
}