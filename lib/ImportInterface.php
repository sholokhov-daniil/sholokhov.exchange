<?php

namespace Sholokhov\Exchange;

use Sholokhov\Exchange\Messages\DataResultInterface;

/**
 * Структура импорта данных
 *
 * @package Import
 */
interface ImportInterface extends ExchangeInterface
{
    /**
     * Проверка наличия элемента сущности
     *
     * @param array $item
     * @return bool
     */
    public function exists(array $item): bool;

    /**
     * Обновление элемента сущности
     *
     * @param array $item
     * @return DataResultInterface
     */
    public function update(array $item): DataResultInterface;

    /**
     * Добавление нового элемента сущности
     *
     * @param array $item
     * @return DataResultInterface
     */
    public function add(array $item): DataResultInterface;

    /**
     * Деактивация элементов сущности, которые не пришли в обмене
     *
     * @return void
     */
    public function deactivate(): void;
}