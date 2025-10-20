<?php

namespace Sholokhov\Exchange\Target\Export;

use XMLWriter;

use Sholokhov\Exchange\AbstractExchange;
use Sholokhov\Exchange\ExchangeMapTrait;
use Sholokhov\Exchange\Messages\Type\Error;
use Sholokhov\Exchange\Fields\FieldInterface;
use Sholokhov\Exchange\Target\Options\Export\XmlOption;
use Sholokhov\Exchange\Fields\Export\XmlFieldInterface;
use Sholokhov\Exchange\Messages\ExchangeResultInterface;
use Sholokhov\Exchange\Preparation\FieldPreparationPipelineInterface;
use Sholokhov\Exchange\Factory\Exchange\FieldPreparationPipelineFactory;

use Bitrix\Main\Text\Encoding;

/**
 * @todo Сделать процессор, который позволит использовать разные механизмы экспорта
 */
class Xml extends AbstractExchange
{
    use ExchangeMapTrait;

    /**
     * Кодировка xml файла
     */
    private const string TARGET_ENCODING = 'utf-8';
    private const string INDENT_STRING = '  ';
    private readonly FieldPreparationPipelineInterface $pipeline;
    private ?XMLWriter $writer = null;

    protected XmlOption $options;

    public function __construct(XmlOption $options = null)
    {
        $this->options = $options ?? new XmlOption;
        $this->pipeline = FieldPreparationPipelineFactory::create($this);
    }

    /**
     * Определение множественности значения поля
     * @param FieldInterface $field
     * @return bool
     */
    public function isMultipleField(FieldInterface $field): bool
    {
        return $field instanceof XmlFieldInterface && $field->getChildrenTag();
    }

    /**
     * Логика экспорта данных
     * @param iterable $source
     * @param ExchangeResultInterface $result
     * @return void
     */
    protected function logic(iterable $source, ExchangeResultInterface $result): void
    {
        $this->initializeWriter();
        $this->writeDocumentHeader();
        $this->processItems($source, $result);
        $this->finalizeDocument();
    }

    /**
     * Инициализация XMLWriter для потоковой записи
     * @return void
     */
    private function initializeWriter(): void
    {
        $this->writer = new XMLWriter();
        $this->writer->openUri($this->options->savePath);
        $this->writer->setIndent(true);
        $this->writer->setIndentString(self::INDENT_STRING);
    }

    /**
     * Запись заголовка XML документа
     * @return void
     */
    private function writeDocumentHeader(): void
    {
        $this->writer->startDocument($this->options->version, self::TARGET_ENCODING);
        $this->writer->startElement($this->options->rootTag);
    }

    /**
     * Обработка элементов источника данных
     * @param iterable $source
     * @param ExchangeResultInterface $result
     * @return void
     */
    private function processItems(iterable $source, ExchangeResultInterface $result): void
    {
        foreach ($source as $item) {
            if (!$this->validateItem($item, $result)) {
                continue;
            }

            $processedItem = $this->prepareItemData($item, $result);

            if ($processedItem === null) {
                continue;
            }

            $this->writeItem($processedItem);
        }
    }

    /**
     * Валидация элемента данных
     * @param mixed $item
     * @param ExchangeResultInterface $result
     * @return bool
     */
    private function validateItem(mixed $item, ExchangeResultInterface $result): bool
    {
        if (!is_array($item)) {
            $this->logInvalidItem();
            $result->addError(new Error('Invalid source item format: expected array'));
            return false;
        }

        return true;
    }

    /**
     * Подготовка данных элемента (кодирование при необходимости)
     * @param array $item
     * @param ExchangeResultInterface $result
     * @return array|null
     */
    private function prepareItemData(array $item, ExchangeResultInterface $result): ?array
    {
        if (!$this->shouldConvertEncoding()) {
            return $item;
        }

        $converted = Encoding::convertEncoding(
            $item,
            $this->options->sourceCharset,
            self::TARGET_ENCODING
        );

        if (!$this->validateItem($converted, $result)) {
            return null;
        }

        return $converted;
    }

    /**
     * Запись элемента в XML
     *
     * @param array $item
     * @return void
     */
    private function writeItem(array $item): void
    {
        $this->writer->startElement($this->options->elementTag);
        $this->writeItemAttributes($item);
        $this->writeItemFields($item);
        $this->writer->endElement();
    }

    /**
     * Запись атрибутов элемента
     *
     * @param array $item
     * @return void
     */
    private function writeItemAttributes(array $item): void
    {
        if (empty($this->options->elementAttributes)) {
            return;
        }

        $preparedAttributes = $this->pipeline->prepare($item, $this->options->elementAttributes);

        foreach ($preparedAttributes as $name => $data) {
            $value = is_array($data) ? ($data['value'] ?? '') : $data;
            $this->writer->writeAttribute($name, (string) $value);
        }
    }

    /**
     * Запись полей элемента
     *
     * @param array $item
     * @return void
     */
    private function writeItemFields(array $item): void
    {
        $fieldMapping = $this->getMappingRegistry()->getFields();
        $preparedFields = $this->pipeline->prepare($item, $fieldMapping);

        foreach ($preparedFields as $tagName => $fieldData) {
            $this->writeField($tagName, $fieldData);
        }
    }

    /**
     * Запись отдельного поля
     * @param string $tagName
     * @param mixed $fieldData
     * @return void
     */
    private function writeField(string $tagName, mixed $fieldData): void
    {
        $this->writer->startElement($tagName);

        if (is_array($fieldData)) {
            $this->writeComplexField($fieldData);
        } else {
            $this->writeSimpleField($fieldData);
        }

        $this->writer->endElement();
    }

    /**
     * Запись сложного поля с атрибутами и вложенными элементами
     * @param array $fieldData
     * @return void
     */
    private function writeComplexField(array $fieldData): void
    {
        $this->writeFieldAttributes($fieldData);
        $this->writeFieldValue($fieldData);
    }

    /**
     * Запись атрибутов поля
     * @param array $fieldData
     * @return void
     */
    private function writeFieldAttributes(array $fieldData): void
    {
        $attributes = $fieldData['attributes'] ?? [];

        if (!is_array($attributes) || empty($attributes)) {
            return;
        }

        foreach ($attributes as $name => $value) {
            $this->writer->writeAttribute((string) $name, (string) $value);
        }
    }

    /**
     * Запись значения поля
     * @param array $fieldData
     * @return void
     */
    private function writeFieldValue(array $fieldData): void
    {
        $value = $fieldData['value'] ?? null;
        $childrenTag = $fieldData['children_tag'] ?? null;

        if (is_array($value) && $childrenTag) {
            $this->writeMultipleValues($value, $childrenTag);
        } elseif (is_array($value)) {
            $this->writeArrayAsText($value);
        } else {
            $this->writeSimpleField($value);
        }
    }

    /**
     * Запись множественных значений
     * @param array $values
     * @param string $childTag
     * @return void
     */
    private function writeMultipleValues(array $values, string $childTag): void
    {
        foreach ($values as $value) {
            $this->writeField($childTag, $value);
        }
    }

    /**
     * Запись массива как текста
     * @param array $values
     * @return void
     */
    private function writeArrayAsText(array $values): void
    {
        $text = implode(PHP_EOL, array_map(strval(...), $values));
        if ($text) {
            $this->writer->text($text);
        }
    }

    /**
     * Запись простого значения
     * @param mixed $value
     * @return void
     */
    private function writeSimpleField(mixed $value): void
    {
        $text = (string)$value;
        if ($text) {
            $this->writer->text($text);
        }
    }

    /**
     * Финализация документа
     * @return void
     */
    private function finalizeDocument(): void
    {
        $this->writer->endElement();
        $this->writer->endDocument();
        $this->writer->flush();
    }

    /**
     * Логирование невалидного элемента
     * @return void
     */
    private function logInvalidItem(): void
    {
        $this->logger?->warning('Invalid source item: expected array format');
    }

    /**
     * Проверка необходимости конвертации кодировки
     * @return bool
     */
    private function shouldConvertEncoding(): bool
    {
        return $this->options->sourceCharset <> self::TARGET_ENCODING;
    }
}