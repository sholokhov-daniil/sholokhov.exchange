<?php

namespace Sholokhov\Exchange\Repository\Result;

use Stringable;

/**
 * Производит хранение результатов обмена в памяти.
 *
 * Данный способ хранения подходит для маленьких обменов.
 * Если данный способ хранения будет использоваться в импорте с большим массивом данных,
 * то для этого лучше выбрать {@see UidRepository}
 *
 * @package Repository
 */
class SimpleResultRepository implements ResultRepositoryInterface
{
    /**
     * Элементы с которыми производилось взаимодействие обменом
     *
     * @var array
     */
    private array $items = [];

    /**
     * Добавить новое значение в хранилище
     *
     * @param string|Stringable $value
     * @return void
     */
    public function add(string|Stringable $value): void
    {
        $this->items[] = (string)$value;
    }

    /**
     * Получение всех значений
     *
     * @return array
     */
    public function get(): array
    {
        return $this->items;
    }
}