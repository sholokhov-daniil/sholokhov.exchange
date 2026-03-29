<?php

namespace Sholokhov\Exchange\Reader;

use Sholokhov\Exchange\Exception\Reader\ReaderException;

/**
 * Reader для работы с данными, хранящимися в памяти.
 *
 * Позволяет использовать массивы, строки и скалярные значения
 * в качестве источника данных, преобразуя их во внутреннюю строку.
 *
 * При каждом вызове {@see read()} создаётся новый поток (stream),
 * содержащий текущее состояние данных.
 *
 * Поток является независимым, поэтому его изменение вне класса
 * не влияет на внутреннее состояние Reader.
 *
 * @package Reader
 */
class MemoryReader implements DataReaderInterface
{
    /**
     * Содержимое потока данных
     *
     * @var string
     */
    private string $content;

    /**
     * @param array|string|int|float $data Исходные данные
     */
    public function __construct(array|string|int|float $data)
    {
        $this->content = $this->normalize($data);
    }

    /**
     * Возвращает поток с данными
     *
     * При каждом вызове создаётся новый поток с текущим содержимым.
     *
     * @return resource Поток для чтения
     *
     * @throws ReaderException Если не удалось создать поток
     */
    public function read()
    {
        $stream = fopen('php://temp', 'r+');

        if (!$stream) {
            throw new ReaderException("Unable to open memory stream");
        }

        fwrite($stream, $this->content);
        rewind($stream);


        return $stream;
    }

    /**
     * Нормализует входные данные в строку
     *
     * - массив преобразуется в строку через перенос строки
     * - скалярные значения приводятся к строке
     *
     * @param array|string|int|float $value
     *
     * @return string
     */
    private function normalize(array|string|int|float $value): string
    {
        if (is_array($value)) {
            $value = implode("\n", array_map(strval(...), $value));
        }

        return (string)$value;
    }
}