<?php

namespace Sholokhov\Exchange\Source;

use Iterator;
use SplFileObject;

/**
 * Источник данных на csv файла
 *
 * @package Source
 */
class CsvFile implements Iterator
{
    private SplFileObject $file;

    /**
     * @param string $path Путь до файла
     * @param string $encoding Кодировка файла
     */
    public function __construct(
        private readonly string $path,
        private readonly string $encoding = 'UTF-8',
    )
    {
        $this->file = new SplFileObject($this->path);
        $this->file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);
    }

    /**
     * Устанавливают значение, которое больше самой длинной строки в CSV-файле,
     * иначе строка разбивается на части заданной длины, если только место разделения не встретится внутри символов-ограничителей.
     * Длина строк измеряется в символах с учётом символов конца строки, которыми завершаются строки.
     *
     * @param int $length
     * @return $this
     *
     * @see fgetcsv
     */
    public function setLength(int $length): self
    {
        $this->file->setMaxLineLen($length);
        return $this;
    }

    /**
     * Символ-разделитель полей и принимает только один однобайтовый символ
     *
     * @param string $separator
     * @return $this
     *
     * @see fgetcsv
     */
    public function setSeparator(string $separator): self
    {
        [, $enclosure, $escape] = $this->file->getCsvControl();
        $this->file->setCsvControl($separator, $enclosure, $escape);
        return $this;
    }

    /**
     * Устанавливает символ-ограничитель значения поля и принимает только один однобайтовый символ
     *
     * @param string $enclosure
     * @return $this
     *
     * @see fgetcsv
     */
    public function setEnclosure(string $enclosure): self
    {
        [$separator, ,$escape] = $this->file->getCsvControl();
        $this->file->setCsvControl($separator, $enclosure, $escape);

        return $this;
    }

    /**
     * Устанавливает символ экранирования и принимает только один однобайтовый символ или пустую строку.
     * Пустая строка "" отключает внутренний механизм экранирования
     *
     * @param string $escape
     * @return $this
     */
    public function setEscape(string $escape): self
    {
        [$separator, $enclosure] = $this->file->getCsvControl();
        $this->file->setCsvControl($separator, $enclosure, $escape);
        return $this;
    }

    public function current(): ?array
    {
        $row = $this->file->current();
        return is_array($row) ? $row : null;
    }

    public function next(): void
    {
        $this->file->next();
    }

    public function key(): int|false
    {
        return $this->file->key();
    }

    public function valid(): bool
    {
        return $this->file->valid();
    }

    public function rewind(): void
    {
        $this->file->rewind();
    }
}