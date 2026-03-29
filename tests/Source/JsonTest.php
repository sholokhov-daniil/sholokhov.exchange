<?php

namespace Sholokhov\Exchange\Source;

use Sholokhov\Exchange\Reader\MemoryReader;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @package Tests\Source
 */
class JsonTest extends TestCase
{
    #[DataProvider('jsonDataProvider')]
    public function testValidSingleJson(string $json, array $options, array $expected): void
    {
        $reader = new MemoryReader($json);
        $source = new Json($reader, $options);

        $result = [];

        foreach ($source as $item) {
            $result[] = $item;
        }

        $this->assertEquals($expected, $result);
    }

    /**
     * @return array[]
     */
    public static function jsonDataProvider(): array
    {
        return [
            'single object' => [
                '{"name":"John"}',
                [],
                [
                    ['name' => 'John']
                ]
            ],

            'invalid json' => [
                '{"name":"John"',
                [],
                [null]
            ],

            'non array json' => [
                '"string"',
                [],
                [null]
            ],

            'multiple array' => [
                '[1,2,3]',
                ['multiple' => true],
                [1, 2, 3]
            ],

            'single mode wraps array' => [
                '[1,2,3]',
                [],
                [
                    [1, 2, 3]
                ]
            ],

            'source key with multiple' => [
                '{"data":{"items":[1,2,3]}}',
                [
                    'multiple' => true,
                    'source_key' => 'data.items'
                ],
                [1, 2, 3]
            ],

            'source key without multiple' => [
                '{"data":{"items":[1,2,3]}}',
                [
                    'source_key' => 'data.items'
                ],
                [
                    [1, 2, 3]
                ]
            ],

            'wrong source key' => [
                '{"data":{"items":[1,2,3]}}',
                [
                    'source_key' => 'wrong.path'
                ],
                [null]
            ],

            'empty array multiple' => [
                '[]',
                ['multiple' => true],
                []
            ],
        ];
    }
}
