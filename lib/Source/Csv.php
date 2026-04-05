<?php

namespace Sholokhov\Exchange\Source;

use Iterator;
use SplFileObject;

use Sholokhov\Exchange\Reader\DataReaderInterface;
use Sholokhov\Exchange\Exception\Reader\ReaderException;

use Bitrix\Main\Text\Encoding;

/**
 * Источник данных на основе CSV формата.
 *
 * Позволяет последовательно читать строки CSV-файла без загрузки всего файла в память.
 * Поддерживает настройку разделителя, символа-ограничителя, символа экранирования и максимальной длины строки.
 *
 * @final
 * @package Source
 */
final class Csv implements Iterator
{
    /**
     * Текущая строка CSV
     *
     * @var array|null
     */
    private ?array $current = null;

    /**
     * Индекс текущей строки
     *
     * @var int
     */
    private int $key = 0;

    /**
     * Максимальная длина строки
     *
     * @var int
     */
    private int $length = 0;

    /**
     * Символ разделителя полей
     *
     * @var string
     */
    private string $separator = ",";

    /**
     * Символ ограничителя поля
     *
     * @var string
     */
    private string $enclosure = "\"";

    /**
     * Символ экранирования
     *
     * @var string
     */
    private string $escape = '\\';

    /**
     * Ресурс потока CSV
     *
     * @var resource|null
     */
    private $resource = null;

    /**
     * Пропускать ли первую строку (заголовок)
     *
     * @var bool
     */
    private bool $skipHeader = true;

    /**
     * Провайдер данных
     *
     * @var DataReaderInterface
     */
    private DataReaderInterface $reader;

    /**
     * Кодировка в которую необходимо конвертировать
     *
     * @var string
     */
    private string $encoding;

    /**
     * @param DataReaderInterface $reader Провайдер данных
     * @param string|null $encoding Кодировка файла
     */
    public function __construct(DataReaderInterface $reader, ?string $encoding = null)
    {
        $this->reader = $reader;
        $this->encoding = $encoding ?? SITE_CHARSET;
    }

    public function __destruct()
    {
        if (is_resource($this->resource)) {
            fclose($this->resource);
        }
    }

    /**
     * Указывает, нужно ли пропускать заголовок CSV
     *
     * @param bool $skipHeader
     * @return $this
     */
    public function setSkipHeader(bool $skipHeader = false): static
    {
        $this->skipHeader = $skipHeader;
        return $this;
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
        $this->length = $length;
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
        $this->separator = $separator;
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
        $this->enclosure = $enclosure;
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
        $this->escape = $escape;
        return $this;
    }

    /**
     * Возвращает текущую строку CSV
     *
     * @return array|null
     */
    public function current(): ?array
    {
        return $this->current;
    }

    /**
     * Возвращает индекс текущей строки
     *
     * @return int
     */
    public function key(): int
    {
        return $this->key;
    }

    /**
     * Переходит к следующей строке CSV
     *
     * @return void
     * @throws ReaderException
     */
    public function next(): void
    {
        $this->current = $this->read();
        $this->key++;
    }

    /**
     * Перематывает итератор в начало CSV
     *
     * @return void
     * @throws ReaderException
     */
    public function rewind(): void
    {
        $resource = $this->getResource();

        if (stream_get_meta_data($resource)['seekable']) {
            rewind($resource);
        }

        $this->key = 0;

        // Пропускаем заголовок только если нужно
        if ($this->skipHeader) {
            $this->read();
        }

        // Читаем первую строку данных
        $this->current = $this->read();
    }

    /**
     * Проверяет, есть ли текущая строка
     *
     * @return bool
     */
    public function valid(): bool
    {
        return $this->current() !== null;
    }

    /**
     * Читает текущую строку CSV из потока переводя внутренний указатель потока на следующую строку
     *
     * @return array|null
     * @throws ReaderException
     */
    private function read(): ?array
    {
        $line = fgetcsv($this->getResource(), $this->length, $this->separator, $this->enclosure, $this->escape);

        if (!is_array($line)) {
            return null;
        }

        foreach ($line as &$value) {
            if ($value !== null && $value !== '') {
                // Определяем кодировку строки
                $detected = mb_detect_encoding($value, ['UTF-8', 'CP1251', 'WINDOWS-1251', 'ISO-8859-1'], true);

                // Если определена и отличается от нужной — конвертируем через Bitrix
                if ($detected !== false && strtoupper($detected) !== strtoupper($this->encoding)) {
                    $value = Encoding::convertEncoding($value, $detected, $this->encoding);
                }
            }
        }
        unset($value);

        return $line;
    }

    /**
     * Возвращает поток CSV
     *
     * @return resource
     * @throws ReaderException
     */
    private function getResource()
    {
        return $this->resource ??= $this->reader->read();
    }
}