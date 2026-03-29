<?php

namespace Sholokhov\Exchange\Reader;

use Sholokhov\Exchange\Exception\Reader\ReaderException;

use PHPUnit\Framework\TestCase;

/**
 * Тесты для {@see MemoryReader}
 *
 * Проверяет:
 * - корректную нормализацию входных данных
 * - создание потока
 * - независимость потоков
 * - корректную работу с различными типами данных
 */
class MemoryReaderTest extends TestCase
{
    /**
     * Проверяет чтение строки
     *
     * @return void
     * @throws ReaderException
     */
    public function testReadString(): void
    {
        $reader = new MemoryReader('hello');

        $stream = $reader->read();
        $content = stream_get_contents($stream);

        $this->assertSame('hello', $content);
    }

    /**
     * Проверяет преобразование массива в строку
     *
     * @return void
     * @throws ReaderException
     */
    public function testReadArray(): void
    {
        $reader = new MemoryReader(['a', 'b', 'c']);

        $stream = $reader->read();
        $content = stream_get_contents($stream);

        $this->assertSame("a\nb\nc", $content);
    }

    /**
     * Проверяет работу со скалярными значениями
     *
     * @return void
     * @throws ReaderException
     */
    public function testReadScalar(): void
    {
        $reader = new MemoryReader(123);

        $stream = $reader->read();
        $content = stream_get_contents($stream);

        $this->assertSame('123', $content);
    }

    /**
     * Проверяет, что каждый вызов read() возвращает новый поток
     *
     * @return void
     * @throws ReaderException
     */
    public function testEachReadReturnsNewStream(): void
    {
        $reader = new MemoryReader('test');

        $stream1 = $reader->read();
        $stream2 = $reader->read();

        $this->assertNotSame($stream1, $stream2);
    }

    /**
     * Проверяет независимость потоков
     *
     * Изменение одного потока не должно влиять на последующие вызовы read()
     *
     * @return void
     * @throws ReaderException
     */
    public function testStreamIndependence(): void
    {
        $reader = new MemoryReader('original');

        $stream1 = $reader->read();
        fwrite($stream1, 'changed');
        rewind($stream1);

        $stream2 = $reader->read();
        $content2 = stream_get_contents($stream2);

        $this->assertSame('original', $content2);
    }

    /**
     * Проверяет работу с пустой строкой
     *
     * @return void
     * @throws ReaderException
     */
    public function testEmptyString(): void
    {
        $reader = new MemoryReader('');

        $stream = $reader->read();
        $content = stream_get_contents($stream);

        $this->assertSame('', $content);
    }
}
