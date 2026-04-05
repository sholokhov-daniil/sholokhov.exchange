<?php

namespace Sholokhov\Exchange\Source;

use Sholokhov\Exchange\Reader\MemoryReader;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @package Tests\Source
 */
class SerializeItemTest extends TestCase
{
    /**
     * Тестирует итерацию по множественным элементам.
     *
     * @param string $serialized Сериализованные данные
     * @param array  $expected   Ожидаемый результат после итерации
     *
     * @return void
     */
    #[DataProvider('multipleDataProvider')]
    public function testIterationMultiple(string $serialized, array $expected): void
    {
        $reader = new MemoryReader($serialized);
        $source = new SerializeItem($reader, true);

        // Сохраняем ключи массива
        $result = iterator_to_array($source, true);

        $this->assertEquals($expected, $result);
    }

    /**
     * Тестирует итерацию по одиночному элементу (не множественному).
     *
     * @return void
     */
    public function testIterationSingle(): void
    {
        $data = serialize('hello');
        $reader = new MemoryReader($data);

        $source = new SerializeItem($reader, false);

        $rows = iterator_to_array($source);
        $this->assertEquals(['hello'], $rows);
    }

    /**
     * Тестирует корректное извлечение всех элементов итератора.
     * Используется вместо устаревшего метода fetch().
     *
     * @return void
     */
    public function testIterationFetchReplacement(): void
    {
        $data = serialize([10, 20, 30]);
        $reader = new MemoryReader($data);

        $source = new SerializeItem($reader, true);

        // получаем все элементы итератора
        $values = iterator_to_array($source);

        $this->assertEquals([10, 20, 30], $values);
    }

    /**
     * Тестирует корректность работы методов rewind() и key().
     * Проверяет, что после rewind() ключ текущей позиции изменился.
     *
     * @return void
     */
    public function testRewindAndKey(): void
    {
        $data = serialize([100, 200]);
        $reader = new MemoryReader($data);

        $source = new SerializeItem($reader, true);

        $source->next();
        $firstKey = $source->key();
        $source->rewind();
        $this->assertNotEquals($firstKey, $source->key());
    }

    /**
     * Тестирует поведение при некорректной сериализованной строке.
     * Ожидается, что итератор вернет массив с одним элементом false.
     *
     * @return void
     */
    public function testInvalidSerializedString(): void
    {
        $data = 'invalid_string';
        $reader = new MemoryReader($data);

        $source = new SerializeItem($reader, true);

        // Проверяем, что при некорректной строке возвращается массив с false
        $rows = iterator_to_array($source);
        $this->assertEquals([false], $rows);
    }

    /**
     * Провайдер данных для testIterationMultiple.
     *
     * @return array
     */
    public static function multipleDataProvider(): array
    {
        return [
            'simple array' => [
                serialize([1, 2, 3]),
                [1, 2, 3],
            ],
            'assoc array' => [
                serialize(['a' => 'x', 'b' => 'y']),
                ['a' => 'x', 'b' => 'y'],
            ],
        ];
    }
}
