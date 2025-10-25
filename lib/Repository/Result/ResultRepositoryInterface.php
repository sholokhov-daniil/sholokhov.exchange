<?php

namespace Sholokhov\Exchange\Repository\Result;

use Stringable;

/**
 * Хранилище результата обмена
 */
interface ResultRepositoryInterface
{
    /**
     * Получение элементов, которые приняли участие в обмене
     *
     * @return mixed
     */
    public function get(): mixed;

    /**
     * Добавление значение в хранилище
     *
     * @param Stringable|string $value Добавляемое значение в хранилище
     * @return void
     */
    public function add(Stringable|string $value): void;
}