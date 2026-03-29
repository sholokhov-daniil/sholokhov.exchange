<?php

namespace Sholokhov\Exchange\Source;

use Sholokhov\Exchange\Helper\Helper;
use Sholokhov\Exchange\Reader\LocalFileReader;
use Sholokhov\Exchange\Exception\Reader\ReaderException;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @package Tests\Source
 */
class CsvTest extends TestCase
{
    /**
     * Тестирует корректность итерации по CSV-файлу.
     *
     * Создает объект Csv для указанного файла и разделителя, и проверяет,
     * что все строки из CSV корректно возвращаются при обходе итератором.
     *
     * @param string $fixture Имя CSV файла из папки тестовых файлов
     * @param string $separator Символ разделителя полей в CSV
     * @param array $expected Ожидаемый результат итерации
     *
     * @return void
     */
    #[DataProvider('csvProvider')]
    public function testCsvIteration(string $fixture, string $separator, array $expected): void
    {
        $path = $this->getUploadFolder() . $fixture;
        $reader = new LocalFileReader($path);

        $csv = new Csv($reader);
        $csv->setSeparator($separator);

        $result = [];
        foreach ($csv as $row) {
            $result[] = $row;
        }

        $this->assertEquals($expected, $result);
    }

    /**
     * Проверяет работу методов rewind() и key() итератора CSV.
     *
     * Проверяет, что после вызова next() ключ итератора изменяется,
     * а после rewind() указатель возвращается в начало файла.
     *
     * @return void
     * @throws ReaderException
     */
    public function testRewindAndKey(): void
    {
        $path = $this->getUploadFolder() . 'simple.csv';
        $reader = new LocalFileReader($path);

        $csv = new Csv($reader);

        $csv->next(); // идем на вторую строку
        $firstKey = $csv->key();
        $csv->rewind();
        $this->assertNotEquals($firstKey, $csv->key());
    }

    /**
     * Проверяет работу установки символов ограничителя и экранирования.
     *
     * Устанавливает enclosure и escape, чтобы корректно читать поля с кавычками
     * и проверяет, что содержимое ячеек совпадает с ожидаемым.
     *
     * @return void
     */
    public function testSetEnclosureAndEscape(): void
    {
        $path = $this->getUploadFolder() . 'quoted.csv';
        $reader = new LocalFileReader($path);

        $csv = new Csv($reader);
        $csv->setEnclosure('"');
        $csv->setEscape('\\');

        $rows = iterator_to_array($csv);

        $this->assertEquals('She said "Hi!"', $rows[1][2]);
    }

    /**
     * Тестируем игнорирование заголовка csv
     *
     * @return void
     * @throws ReaderException
     */
    public function testSetSkipHeader(): void
    {
        $path = $this->getUploadFolder() . 'simple.csv';
        $reader = new LocalFileReader($path);

        $csv = new Csv($reader);
        $csv->setSkipHeader(true);

        $rows = iterator_to_array($csv);

        // Проверяем, что строки всё ещё читаются корректно
        $this->assertEquals('Alice', $rows[0][1]);

        $csv->setSkipHeader(false);
        $csv->rewind();

        $rows = iterator_to_array($csv);
        $this->assertEquals(['id', 'name', 'age'], $rows[0]);
    }

    /**
     * Тестирование установки длины читаемой строки
     *
     * @return void
     */
    public function testSetLength(): void
    {
        $path = $this->getUploadFolder() . 'simple.csv';
        $reader = new LocalFileReader($path);

        $csv = new Csv($reader);

        $csv->setLength(12); // ставим ограничение
        $rows = iterator_to_array($csv);

        // Проверяем, что строки всё ещё читаются корректно
        $this->assertEquals('Alice', $rows[0][1]);
    }

    /**
     * Проверка конвертирования содержимого из других кодировок
     *
     * @return void
     */
    public function testEncoding(): void
    {
        $path = $this->getUploadFolder() . 'cp1251.csv';
        $reader = new LocalFileReader($path);

        $csv = new Csv($reader, 'UTF-8');
        $rows = iterator_to_array($csv);

        $this->assertCount(1, $rows);
        $this->assertEquals(['Даниил', 'Шолохов'], $rows[0]);
    }

    /**
     * Провайдер данных для теста итерации CSV-файла.
     *
     * Возвращает набор тестовых файлов CSV с различными разделителями и ожидаемыми результатами.
     *
     * @return array[]
     */
    public static function csvProvider(): array
    {
        return [
            'simple comma' => [
                'simple.csv', // fixture
                ',',          // separator
                [
                    ['1', 'Alice', '30'],
                    ['2', 'Bob', '25'],
                    ['3', 'Charlie', '40'],
                ],
            ],
            'semicolon separator' => [
                'semicolon.csv',
                ';',
                [
                    ['1', 'Alice', 'NY'],
                    ['2', 'Bob', 'LA'],
                ],
            ],
            'quoted fields' => [
                'quoted.csv',
                ',',
                [
                    ['1', 'Alice', 'Hello, world'],
                    ['2', 'Bob', 'She said "Hi!"'],
                ],
            ],
        ];
    }

    /**
     * Возвращает папку с тестовыми файлами
     *
     * @return string
     */
    private function getUploadFolder(): string
    {
        return Helper::getRootDir() . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'csv' . DIRECTORY_SEPARATOR;
    }
}
