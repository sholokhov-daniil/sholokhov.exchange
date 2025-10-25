<?php

namespace Sholokhov\Exchange\Messages\Type;

/**
 * Результат вызова события обмена
 *
 * @package Message
 */
class EventResult extends Result
{
    /**
     * Обмен значением остановлено
     *
     * @var bool
     */
    private bool $stopped = false;

    /**
     * Проверка остановки обмена значением
     *
     * @return bool
     */
    public function isStopped(): bool
    {
        return $this->stopped;
    }

    /**
     * Флаг остановки обмена значения
     *
     * @param bool $stopped
     * @return $this
     */
    public function setStopped(bool $stopped = true): self
    {
        $this->stopped = $stopped;
        return $this;
    }
}