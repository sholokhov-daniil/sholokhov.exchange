<?php

namespace Sholokhov\Exchange\Source;

use Iterator;
use SplFileObject;

/**
 * Источник данных на основе CSV-файла.
 *
 * Позволяет последовательно читать строки CSV-файла без загрузки всего файла в память.
 * Поддерживает настройку разделителя, символа-ограничителя, символа экранирования и максимальной длины строки.
 *
 * @package Source
 */
class CsvFile implements Iterator
{
    use IterableTrait;

    /**
     * @param string $path Путь до файла
     * @param string $encoding Кодировка файла
     */
    public function __construct(
        private readonly string $path,
        private readonly string $encoding = 'UTF-8',
    )
    {
    }

    /**
     * Устанавливает максимальную длину читаемой строки CSV.
     *
     * Если строка длиннее установленного значения, она будет обрезана.
     * Длина измеряется в символах с учётом конца строки.
     *
     * @param int $length Максимальная длина строки
     * @return $this
     *
     * @see SplFileObject::setMaxLineLen()
     */
    public function setLength(int $length): self
    {
        $this->getIterator()->setMaxLineLen($length);
        return $this;
    }

    /**
     * Устанавливает символ-разделитель полей.
     *
     * Принимает только один однобайтовый символ.
     *
     * @param string $separator Символ-разделитель
     * @return $this
     *
     * @see SplFileObject::setCsvControl()
     */
    public function setSeparator(string $separator): self
    {
        [, $enclosure, $escape] = $this->getIterator()->getCsvControl();
        $this->getIterator()->setCsvControl($separator, $enclosure, $escape);
        return $this;
    }

    /**
     * Устанавливает символ-ограничитель значения поля.
     *
     * Принимает только один однобайтовый символ.
     *
     * @param string $enclosure Символ-ограничитель
     * @return $this
     *
     * @see SplFileObject::setCsvControl()
     */
    public function setEnclosure(string $enclosure): self
    {
        [$separator, ,$escape] = $this->getIterator()->getCsvControl();
        $this->getIterator()->setCsvControl($separator, $enclosure, $escape);

        return $this;
    }

    /**
     * Устанавливает символ экранирования для полей CSV.
     *
     * Пустая строка "" отключает механизм экранирования.
     *
     * @param string $escape Символ экранирования
     * @return $this
     *
     * @see SplFileObject::setCsvControl()
     */
    public function setEscape(string $escape): self
    {
        [$separator, $enclosure] = $this->getIterator()->getCsvControl();
        $this->getIterator()->setCsvControl($separator, $enclosure, $escape);
        return $this;
    }

    /**
     * Возвращает текущую строку CSV-файла.
     *
     * @return array|null Массив значений строки или null, если строка невалидна
     *
     * @inheritDoc
     * @see Iterator::current()
     */
    public function current(): ?array
    {
        $row = $this->getIterator()->current();
        return is_array($row) ? $row : null;
    }

    /**
     * Инициализация внутреннего итератора для чтения CSV-файла.
     *
     * @return SplFileObject Итератор для чтения CSV
     */
    protected function load(): SplFileObject
    {
        $file = new SplFileObject($this->path);
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);

        return $file;
    }

    /**
     * Получение итератора CSV-файла.
     *
     * @return SplFileObject Итератор CSV-файла
     */
    protected function getIterator(): SplFileObject
    {
        return $this->iterator ??= $this->load();
    }
}