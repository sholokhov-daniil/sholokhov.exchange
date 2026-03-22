<?php

namespace Sholokhov\Exchange\Source;

use Sholokhov\Exchange\Exception\Source\InvalidJsonFileException;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @package Tests\Source
 */
class JsonFileTest extends TestCase
{
    /**
     * Проверка корректности парсинга json и его итерация
     *
     * @param string $json
     * @param array $options
     * @param array $expected
     *
     * @return void
     */
    #[DataProvider('jsonFileProvider')]
    public function testJsonFile(string $json, array $options, array $expected): void
    {
        // Передаем loader, который просто возвращает наш JSON
        $loader = fn(string $path) => $json;

        $source = new JsonFile('fake/path.json', $options, $loader);

        $result = iterator_to_array($source);

        $this->assertEquals($expected, $result);
    }

    /**
     * Проверка на loader, который возвращает пустую строку вместо json
     *
     * @return void
     */
    public function testEmptyLoader(): void
    {
        // loader возвращает пустую строку
        $loader = fn(string $path) => '';

        $source = new JsonFile('fake.json', [], $loader);

        $result = iterator_to_array($source);

        $this->assertEquals([null], $result);
    }

    /**
     * Проверка исключения при некорректном возвращаемом типе данных загрузчика содержимого
     *
     * @param callable $loader
     *
     * @return void
     * @throws InvalidJsonFileException
     */
    #[DataProvider('invalidLoaderProvider')]
    public function testInvalidLoader(callable $loader): void
    {
        $this->expectException(InvalidJsonFileException::class);
        $this->expectExceptionMessage('Invalid json file');

        new JsonFile('fake.json', [], $loader);
    }

    /**
     * Данные, для тестирования парсинга json
     *
     * @return array[]
     */
    public static function jsonFileProvider(): array
    {
        return [
            'valid multiple' => [
                '[1,2,3]',
                ['multiple' => true],
                [1, 2, 3]
            ],

            'valid single mode' => [
                '[1,2,3]',
                [],
                [
                    [1, 2, 3]
                ]
            ],

            'invalid json' => [
                '{"broken":', // невалидный JSON
                [],
                [null]
            ],

            'source key multiple' => [
                '{"data": {"items": [1,2,3]}}',
                [
                    'multiple' => true,
                    'source_key' => 'data.items'
                ],
                [1, 2, 3]
            ],

            'source key single' => [
                '{"data": {"items": [1,2,3]}}',
                [
                    'source_key' => 'data.items'
                ],
                [
                    [1, 2, 3]
                ]
            ],
        ];
    }

    /**
     * Список невалидных загрузчиков контента
     *
     * @return \Closure[][]
     */
    public static function invalidLoaderProvider(): array
    {
        return [
            'integer' => [
                static fn() => 356,
            ],
            'array' => [
                static fn() => [],
            ],
            'null' => [
                static fn() => null,
            ],
            'object' => [
                static fn() => new \stdClass,
            ],
        ];
    }
}
