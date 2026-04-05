<?php

namespace Sholokhov\Exchange\Source;

use Sholokhov\Exchange\Helper\Helper;
use Sholokhov\Exchange\Reader\LocalFileReader;

use PHPUnit\Framework\TestCase;

/**
 * Тестирование источника данных Xml.
 *
 * Класс проверяет корректность работы Xml с различными типами XML-файлов:
 * - простой список элементов
 * - кастомный корневой тег
 * - пустой файл
 * - итерация по элементам
 * - отсутствие нужного корневого тега
 * - сложная вложенная структура
 *
 * @package Tests\Source
 */
class XmlTest extends TestCase
{
    /**
     * Тестирует корректное чтение XML с простым списком элементов.
     *
     * @return void
     */
    public function testParseSimpleXml(): void
    {
        $filePath = $this->getUploadFolder() . 'simple.xml';
        $reader = new LocalFileReader($filePath);

        $source = new Xml($reader);
        $source->setRootTag('data');

        $result = iterator_to_array($source);

        $this->assertCount(2, $result);
        $this->assertEquals('1', $result[0]['item_id']);
        $this->assertEquals('Alice', $result[0]['item_name']);
        $this->assertEquals('2', $result[1]['item_id']);
        $this->assertEquals('Bob', $result[1]['item_name']);
    }

    /**
     * Тестирует поведение источника при пустом XML-файле.
     *
     * @return void
     */
    public function testEmptyFile(): void
    {
        $filePath = $this->getUploadFolder() . 'empty.xml';
        $reader = new LocalFileReader($filePath);
        $source = new Xml($reader);

        $this->assertEmpty(iterator_to_array($source));
    }

    /**
     * Тестирует работу с кастомным корневым тегом.
     *
     * @return void
     */
    public function testCustomRootTag(): void
    {
        $filePath = $this->getUploadFolder() . 'custom_root.xml';
        $reader = new LocalFileReader($filePath);

        $source = new Xml($reader);
        $source->setRootTag('root');

        $result = iterator_to_array($source);

        $this->assertNotEmpty($result);
        $this->assertEquals('1', $result[0]['item_id']);
        $this->assertEquals('Alice', $result[0]['item_name']);
    }

    /**
     * Тестирует корректное извлечение элементов с указанной глубиной rootTagDepth.
     *
     * Используется для сложных вложенных структур XML, где элементы находятся
     * не напрямую под root, а на определённой глубине.
     *
     * @return void
     */
    public function testRootTagDepth(): void
    {
        $filePath = $this->getUploadFolder() . 'complex_2.xml';
        $reader = new LocalFileReader($filePath);

        $source = new Xml($reader);
        $source->setRootTag('items');
        $source->setRootTagDepth(3);

        $result = iterator_to_array($source);

        $this->assertCount(2, $result);
        // Проверяем первый элемент
        $this->assertEquals('15', $result[0]['item_attribute_id']);
        $this->assertEquals('Тут название', $result[0]['item_name']);
        $this->assertEquals('Черный', $result[0]['item_color']);
        $this->assertEquals('33', $result[0]['item_color_attribute_id']);

        // Проверяем второй элемент
        $this->assertArrayNotHasKey('item_attribute_id', $result[1]); // второго элемента нет id
        $this->assertEquals('Тут название2', $result[1]['item_name']);
        $this->assertEquals('Белый', $result[1]['item_color']);
        $this->assertEquals('12', $result[1]['item_color_attribute_id']);
    }

    /**
     * Тестирует итерацию по элементам XML.
     *
     * @return void
     */
    public function testIteration(): void
    {
        $filePath = $this->getUploadFolder() . 'iteration.xml';
        $reader = new LocalFileReader($filePath);

        $source = new Xml($reader);
        $source->setRootTag('data');

        $iterator = iterator_to_array($source);

        $this->assertCount(2, $iterator);
        $this->assertEquals('1', $iterator[0]['item_id']);
        $this->assertEquals('2', $iterator[1]['item_id']);
    }

    /**
     * Тестирует поведение при отсутствии указанного корневого тега.
     *
     * @return void
     */
    public function testMissingRootTag(): void
    {
        $filePath = $this->getUploadFolder() . 'missing_root.xml';
        $reader = new LocalFileReader($filePath);

        $source = new Xml($reader);
        $source->setRootTag('non_existing_tag');

        $this->assertEmpty(iterator_to_array($source));
    }

    /**
     * Тестирует работу с комплексной вложенной структурой XML.
     *
     * @return void
     */
    public function testComplexLogic(): void
    {
        $filePath = $this->getUploadFolder() . 'complex.xml';
        $reader = new LocalFileReader($filePath);

        $source = new Xml($reader);
        $source->setRootTag('items');

        $result = iterator_to_array($source);

        $this->assertCount(2, $result);

        // Проверяем первый элемент
        $this->assertEquals(15, $result[0]['item_attribute_id']);
        $this->assertEquals('Тут название', $result[0]['item_name']);
        $this->assertEquals(33, $result[0]['item_color_attribute_id']);
        $this->assertEquals('Черный', $result[0]['item_color']);

        // Проверяем второй элемент
        $this->assertArrayNotHasKey('item_attribute_id', $result[1]);
        $this->assertEquals('Тут название2', $result[1]['item_name']);
        $this->assertEquals(12, $result[1]['item_color_attribute_id']);
        $this->assertEquals('Белый', $result[1]['item_color']);
    }

    /**
     * Возвращает путь к папке с XML-файлами для тестирования.
     *
     * Используется для всех тестов: $this->getUploadFolder(). 'file.xml'
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
