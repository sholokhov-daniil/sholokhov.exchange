<?php

namespace Sholokhov\Exchange\Source\Entities\IBlock;

use CIBlockResult;

use Sholokhov\Exchange\Provider\Entity\IBlockProviderInterface;

use Bitrix\Main\Loader;

use PHPUnit\Framework\TestCase;

/**
 * Проверяет корректность работы источника данных на основе элементов информационного блока (IBlock).
 *
 * @package Test\Source
 */
class SectionTest extends TestCase
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
        Loader::includeModule('iblock');

        // Массив элементов для батчей
        $allElements = [
            ['ID' => 1, 'NAME' => 'Alice'],
            ['ID' => 2, 'NAME' => 'Bob']
        ];

        // Мок CIBlockResult
        $resultMock = $this->createMock(CIBlockResult::class);
        $resultMock->method('Fetch')->willReturnCallback(function () use (&$allElements) {
            return array_shift($allElements) ?: null;
        });

        // Мок провайдера
        $providerMock = $this->createMock(IBlockProviderInterface::class);
        $providerMock->method('setFilter')->willReturnSelf();
        $providerMock->method('setOrder')->willReturnSelf();
        $providerMock->method('setSelect')->willReturnSelf();
        $providerMock->method('setLimit')->willReturnSelf();
        $providerMock->method('query')->willReturn($resultMock);

        // Создаем объект Element с моканным провайдером
        $source = new Section($providerMock);

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

        $this->assertEquals(2, $items[1]['ID']);
        $this->assertEquals('Bob', $items[1]['NAME']);
    }

    public function testPaginationWorks(): void
    {
        Loader::includeModule('iblock');

        $limit = 2;
        $sectionData = [
            ['ID' => 1, 'NAME' => 'Alice'],
            ['ID' => 2, 'NAME' => 'Bob'],
            ['ID' => 3, 'NAME' => 'Charlie'],
            ['ID' => 4, 'NAME' => 'Diana'],
            ['ID' => 5, 'NAME' => 'Eve'],
        ];

        $allSections = $sectionData;

        // Мок провайдера
        $providerMock = $this->createMock(IBlockProviderInterface::class);
        $providerMock->method('setFilter')->willReturnCallback(
            function(array $filter) use (&$allSections, $sectionData, $providerMock, $limit) {
                if (!isset($filter['>ID'])) {
                    return $providerMock;
                }

                $lastId = $filter['>ID'];

                $allSections = array_filter(
                    $sectionData,
                    fn(array $i) => $i['ID'] > $lastId
                );

                $allSections = array_slice($allSections, 0, $limit);

                return $providerMock;
            }
        );

        $providerMock->method('setOrder')->willReturnSelf();
        $providerMock->method('setSelect')->willReturnSelf();
        $providerMock->method('setLimit')->willReturnSelf();
        $providerMock->method('query')
            ->willReturnCallback(function () use (&$allSections) {

                $resultMock = $this->createMock(CIBlockResult::class);

                $localData = $allSections;

                $resultMock->method('Fetch')->willReturnCallback(
                    function () use (&$localData) {
                        return array_shift($localData) ?: null;
                    }
                );

                return $resultMock;
            });

        // Создаём источник с лимитом 2, чтобы проверить батчи
        $source = new Section($providerMock);
        $source->setLimit($limit);

        $items = [];

        foreach ($source as $item) {
            $items[] = $item;
        }

        // Проверяем, что получили все элементы
        $this->assertCount(5, $items);

        // Проверяем данные каждого элемента
        foreach ($sectionData as $index => $data) {
            $this->assertEquals($data['ID'], $items[$index]['ID']);
            $this->assertEquals($data['NAME'], $items[$index]['NAME']);
        }
    }
}
