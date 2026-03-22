<?php

namespace Sholokhov\Exchange\Source\Entities\IBlock;

use CIBlockResult;
use _CIBElement;

use Sholokhov\Exchange\Exception\Source\SourceException;
use Sholokhov\Exchange\Provider\Entity\IBlockElementProviderInterface;

use Bitrix\Main\Loader;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
/**
 * Тесты для класса Element.
 *
 * Проверяет корректность работы источника данных на основе элементов информационного блока (IBlock).
 *
 * @package Test\Source
 */
class ElementTest extends TestCase
{
    /**
     * Проверяет, что конструктор выбрасывает исключение при неправильном фильтре.
     *
     * Использует DataProvider {@see invalidFilterProvider} для передачи различных некорректных опций.
     *
     * @param array $options Набор опций, передаваемых в конструктор Element
     *
     * @return void
     *
     * @throws SourceException Ожидаемое исключение при неверном фильтре
     */
    #[DataProvider('invalidFilterProvider')]
    public function testExceptionFilter(array $options): void
    {
        $this->expectException(SourceException::class);
        new Element($options);
    }

    /**
     * Проверяет работу метода load() через итератор.
     *
     * Тест создает моки элементов _CIBElement и CIBlockResult, а также мок провайдера IBlockElementProviderInterface.
     * Проверяется, что метод возвращает корректный итератор с элементами и их свойствами.
     *
     * @return void
     */
    public function testLoadReturnsIterator(): void
    {
        Loader::includeModule('iblock');

        // Создаем моки элементов _CIBElement
        $element1 = $this->createMock(_CIBElement::class);
        $element1->method('GetFields')->willReturn(['ID' => 1, 'NAME' => 'Alice']);
        $element1->method('GetProperty')->willReturn([]);

        $element2 = $this->createMock(_CIBElement::class);
        $element2->method('GetFields')->willReturn(['ID' => 2, 'NAME' => 'Bob']);
        $element2->method('GetProperty')->willReturn([]);

        // Мок CIBlockResult
        $resultMock = $this->createMock(CIBlockResult::class);
        $resultMock->method('GetNextElement')->willReturnOnConsecutiveCalls(
            $element1,
            $element2,
            null // Конец итерации
        );

        // Мок провайдера
        $providerMock = $this->createMock(IBlockElementProviderInterface::class);
        $providerMock->method('setFilter')->willReturnSelf();
        $providerMock->method('setOrder')->willReturnSelf();
        $providerMock->method('setSelect')->willReturnSelf();
        $providerMock->method('query')->willReturn($resultMock);

        // Создаем объект Element с моканным провайдером
        $sourceOptions = [
            'FILTER' => ['IBLOCK_ID' => 'test'],
            'PROPERTIES' => [
                'PROPERTY1',
                'PROPERTY3',
            ]
        ];
        $elementSource = new Element($sourceOptions, $providerMock);

        $items = iterator_to_array($elementSource);

        // Проверяем количество элементов
        $this->assertCount(2, $items);

        // Проверяем содержимое первого элемента
        $this->assertEquals(1, $items[0]['ID']);
        $this->assertEquals('Alice', $items[0]['NAME']);
        $this->assertEquals([], $items[0]['PROPERTIES']);

        // Проверяем содержимое второго элемента
        $this->assertEquals(2, $items[1]['ID']);
        $this->assertEquals('Bob', $items[1]['NAME']);
        $this->assertEquals([], $items[1]['PROPERTIES']);
    }

    /**
     * Провайдер данных для теста конструктора с некорректным фильтром.
     *
     * Возвращает массив наборов опций, которые должны вызывать исключение SourceException.
     *
     * @return array<int, array<int, array<string, mixed>>> Наборы некорректных опций
     */
    public static function invalidFilterProvider(): array
    {
        return [
            [
                []
            ],
            [
                ['FILTER' => []]
            ]
        ];
    }
}
