<?php

namespace Sholokhov\Exchange\Source;

use Sholokhov\Exchange\Helper\Helper;

use PHPUnit\Framework\TestCase;
use Sholokhov\Exchange\Reader\LocalFileReader;


/**
 * Тестирование источника данных SimpleXml.
 *
 * Класс проверяет корректность работы SimpleXml с различными типами XML:
 * - пустой файл
 * - простой список элементов
 * - кастомный корневой тег
 * - итерация по элементам
 * - отсутствие нужного корневого тега
 * - сложная вложенная структура
 *
 * @package Tests\Source
 */
class SimpleXmlTest extends TestCase
{
    /**
     * Тестирует корректное чтение XML с простым списком элементов.
     *
     * Проверяется:
     * - Количество элементов
     * - Значения каждого элемента
     *
     * @return void
     */
    public function testParseSimpleXml(): void
    {
        $filePath = $this->getUploadFolder() . 'simple.xml';
        $reader = new LocalFileReader($filePath);

        $source = new SimpleXml($reader);
        $source->setRootTag('item');

        $result = iterator_to_array($source);

        $this->assertCount(2, $result);
        $this->assertEquals('1', $result[0]['id']);
        $this->assertEquals('Alice', $result[0]['name']);
        $this->assertEquals('2', $result[1]['id']);
        $this->assertEquals('Bob', $result[1]['name']);
    }

    /**
     * Тестирует поведение источника при пустом XML-файле.
     *
     * Проверяется, что итератор возвращает пустой массив.
     *
     * @return void
     */
    public function testEmptyFile(): void
    {
        $filePath = $this->getUploadFolder() . 'empty.xml';
        $reader = new LocalFileReader($filePath);

        $source = new SimpleXml($reader);

        $this->assertEmpty(iterator_to_array($source));
    }

    /**
     * Тестирует использование кастомного корневого тега rootTag.
     *
     * Проверяется, что данные извлекаются корректно даже при изменённом rootTag.
     *
     * @return void
     */
    public function testCustomRootTag(): void
    {
        $filePath = $this->getUploadFolder() . 'custom_root.xml';
        $reader = new LocalFileReader($filePath);

        $source = new SimpleXml($reader);
        $source->setRootTag('item');
        $result = iterator_to_array($source);

        $this->assertNotEmpty($result);
        $this->assertEquals('1', $result[0]['id']);
        $this->assertEquals('Alice', $result[0]['name']);
    }

    /**
     * Тестирует корректную итерацию по элементам XML.
     *
     * Проверяется, что SimpleXml корректно реализует Iterator:
     * - rewind()
     * - current()
     * - next()
     *
     * @return void
     */
    public function testIteration(): void
    {
        $filePath = $this->getUploadFolder() . 'iteration.xml';
        $reader = new LocalFileReader($filePath);

        $source = new SimpleXml($reader);
        // Тег в iteration.xml — "data"
        $source->setRootTag('item');

        $iterator = iterator_to_array($source);

        $this->assertCount(2, $iterator);
        $this->assertEquals('1', $iterator[0]['id']);
        $this->assertEquals('2', $iterator[1]['id']);
    }

    /**
     * Тестирует поведение при отсутствии указанного корневого тега.
     *
     * Проверяется, что источник возвращает пустой итератор.
     *
     * @return void
     */
    public function testMissingRootTag(): void
    {
        $filePath = $this->getUploadFolder() . 'missing_root.xml';
        $reader = new LocalFileReader($filePath);

        $source = new SimpleXml($reader);
        // Тег, которого нет
        $source->setRootTag('non_existing_tag');

        $this->assertEmpty(iterator_to_array($source));
    }

    /**
     * Тестирует работу с сложной вложенной структурой XML.
     *
     * Пример XML содержит:
     * - элементы с атрибутами (@id)
     * - вложенные массивы (color)
     * - отсутствие некоторых атрибутов
     *
     * @return void
     */
    public function testComplexLogic(): void
    {
        $filePath = $this->getUploadFolder() . 'complex.xml';
        $reader = new LocalFileReader($filePath);

        $source = new SimpleXml($reader);
        $source->setRootTag('items.item');
        $result = iterator_to_array($source);

        $this->assertCount(2, $result);

        // Проверяем первый элемент
        $this->assertEquals(15, $result[0]['@id']);
        $this->assertEquals('Тут название', $result[0]['name']);
        $this->assertEquals(33, $result[0]['color']['@id']);
        $this->assertEquals('Черный', $result[0]['color']['#']);

        // Проверяем второй элемент
        $this->assertArrayNotHasKey('@id', $result[1]); // второго элемента нет id
        $this->assertEquals('Тут название2', $result[1]['name']);
        $this->assertEquals(12, $result[1]['color']['@id']);
        $this->assertEquals('Белый', $result[1]['color']['#']);

    }

    /**
     * Возвращает путь к папке с XML-файлами для тестирования.
     *
     * @return string Путь к директории с тестовыми XML
     */
    private function getUploadFolder(): string
    {
        return Helper::getRootDir()
            . DIRECTORY_SEPARATOR . 'upload'
            . DIRECTORY_SEPARATOR . 'tests'
            . DIRECTORY_SEPARATOR . 'xml'
            . DIRECTORY_SEPARATOR;
    }
}
