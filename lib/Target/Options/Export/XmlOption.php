<?php

namespace Sholokhov\Exchange\Target\Options\Export;

use Sholokhov\Exchange\Fields\FieldInterface;
use Bitrix\Main\Application;

/**
 * Конфигурация экспорта данных в xml формате
 */
class XmlOption
{
    /**
     * Версия xml файла
     *
     * @var string
     */
    public string $version = '1.0';

    /**
     * Кодировка данных источника
     *
     * @var string
     */
    public string $sourceCharset;

    /**
     * Наименование корневого тега
     *
     * @var string
     */
    public string $rootTag = 'root';

    /**
     * Наименование элемента тега вложенного в корневой
     *
     * @var string
     */
    public string $elementTag = 'item';

    /**
     * Путь сохранения файла экспорта
     *
     * @var string
     */
    public string $savePath;

    /**
     * Карта атрибутов элемента
     *
     * Указывается какие атрибуты должны быть у элемента
     *
     * @var FieldInterface[]
     */
    public array $elementAttributes = [];

    public function __construct()
    {
        $this->savePath = $_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/export.xml';

        $culture = Application::getInstance()->getContext()->getCulture();
        $defaultCharset = $culture->getCharset() ?: 'utf-8';
        $this->sourceCharset = $defaultCharset;
    }
}