<?php

namespace Sholokhov\Exchange\Source\Concerns;

use Sholokhov\Exchange\Exception\Source\SourceException;

trait BatchIteratorTrait
{
    /**
     * Текущий батч элементов
     *
     * @var array
     */
    protected array $batch = [];

    /**
     * Признак окончания итерации
     *
     * @var bool
     */
    protected bool $finished = false;

    /**
     * Загружает следующий батч
     *
     * @return void
     */
    abstract protected function fetchBatch(): void;

    /**
     * Возвращает текущий элемент итератора
     *
     * @return mixed
     */
    public function current(): mixed
    {
        return current($this->batch);
    }

    /**
     * Переводит итератор на следующий элемент
     *
     * Если текущий батч закончился, подгружает следующий батч.
     *
     * @return void
     * @throws SourceException
     */
    public function next(): void
    {
        next($this->batch);

        if (!$this->valid()) {
            $this->fetchBatch();
            reset($this->batch);
        }
    }

    /**
     * Возвращает ключ текущего элемента итератора
     *
     * @return mixed
     */
    public function key(): mixed
    {
        return key($this->batch);
    }

    /**
     * Проверяет валидность текущего элемента
     *
     * @return bool true, если элемент существует, false — если батч закончился
     */
    public function valid(): bool
    {
        return !is_null($this->key());
    }

    /**
     * Сбрасывает итератор
     *
     * Перезапускает итерацию с самого начала
     *
     * @return void
     * @throws SourceException
     */
    public function rewind(): void
    {
        $this->resetState();
        $this->finished = false;
        $this->batch = [];
        $this->fetchBatch();
    }

    /**
     * Хук, для расширения логики сброса итератора
     *
     * Метод предназначен для расширения
     *
     * @return void
     */
    protected function resetState(): void
    {
    }
}