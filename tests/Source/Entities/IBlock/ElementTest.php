<?php

namespace Sholokhov\Exchange\Source\Entities\IBlock;

use CIBlockResult;
use _CIBElement;

use Sholokhov\Exchange\Provider\Entity\IBlockElementProviderInterface;

use PHPUnit\Framework\TestCase;
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
     * Проверяет работу метода load() через итератор.
     *
     * Тест создает моки элементов _CIBElement и CIBlockResult, а также мок провайдера IBlockElementProviderInterface.
     * Проверяется, что метод возвращает корректный итератор с элементами и их свойствами.
     *
     * @return void
     */
    public function testLoadReturnsIterator(): void
    {
        // Создаем моки элементов _CIBElement
        $element1 = $this->createMock(_CIBElement::class);
        $element1->method('GetFields')->willReturn(['ID' => 1, 'NAME' => 'Alice']);
        $element1->method('GetProperties')->willReturn([]);

        $element2 = $this->createMock(_CIBElement::class);
        $element2->method('GetFields')->willReturn(['ID' => 2, 'NAME' => 'Bob']);
        $element2->method('GetProperties')->willReturn([]);

        // Массив элементов для батчей
        $allElements = [$element1, $element2];

        // Мок CIBlockResult
        $resultMock = $this->createMock(CIBlockResult::class);
        $resultMock->method('GetNextElement')->willReturnCallback(function () use (&$allElements) {
            return array_shift($allElements) ?: null;
        });

        // Мок провайдера
        $providerMock = $this->createMock(IBlockElementProviderInterface::class);
        $providerMock->method('setFilter')->willReturnSelf();
        $providerMock->method('setOrder')->willReturnSelf();
        $providerMock->method('setSelect')->willReturnSelf();
        $providerMock->method('setLimit')->willReturnSelf();
        $providerMock->method('query')->willReturn($resultMock);

        // Создаем объект Element с моканным провайдером
        $source = new Element($providerMock);

        // Итерация через foreach
        $items = [];
        foreach ($source as $item) {
            $items[] = $item;
        }

        // Проверяем количество элементов
        $this->assertCount(2, $items);

        // Проверяем содержимое
        $this->assertEquals(1, $items[0]['ID']);
        $this->assertEquals('Alice', $items[0]['NAME']);
        $this->assertEquals([], $items[0]['PROPERTIES']);

        $this->assertEquals(2, $items[1]['ID']);
        $this->assertEquals('Bob', $items[1]['NAME']);
        $this->assertEquals([], $items[1]['PROPERTIES']);
    }

    public function testPaginationWorks(): void
    {
        // Создадим 5 элементов
        $elementsData = [
            ['ID' => 1, 'NAME' => 'Alice'],
            ['ID' => 2, 'NAME' => 'Bob'],
            ['ID' => 3, 'NAME' => 'Charlie'],
            ['ID' => 4, 'NAME' => 'Diana'],
            ['ID' => 5, 'NAME' => 'Eve'],
        ];

        $elements = [];
        foreach ($elementsData as $data) {
            $elementMock = $this->createMock(_CIBElement::class);
            $elementMock->method('GetFields')->willReturn($data);
            $elementMock->method('GetProperties')->willReturn([]);
            $elements[] = $elementMock;
        }

        // Разобьём элементы на батчи по 2
        $allElements = $elements;

        // Мок CIBlockResult
        $resultMock = $this->createMock(CIBlockResult::class);
        $resultMock->method('GetNextElement')->willReturnCallback(function () use (&$allElements) {
            return array_shift($allElements) ?: null;
        });

        // Мок провайдера
        $providerMock = $this->createMock(IBlockElementProviderInterface::class);
        $providerMock->method('setFilter')->willReturnSelf();
        $providerMock->method('setOrder')->willReturnSelf();
        $providerMock->method('setSelect')->willReturnSelf();
        $providerMock->method('setLimit')->willReturnSelf();
        $providerMock->method('query')->willReturn($resultMock);

        // Создаём источник с лимитом 2, чтобы проверить батчи
        $source = new Element($providerMock);
        $source->setLimit(2);

        $items = [];
        foreach ($source as $item) {
            $items[] = $item;
        }

        // Проверяем, что получили все элементы
        $this->assertCount(5, $items);

        // Проверяем данные каждого элемента
        foreach ($elementsData as $index => $data) {
            $this->assertEquals($data['ID'], $items[$index]['ID']);
            $this->assertEquals($data['NAME'], $items[$index]['NAME']);
            $this->assertEquals([], $items[$index]['PROPERTIES']);
        }
    }
}
