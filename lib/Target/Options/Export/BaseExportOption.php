<?php

namespace Sholokhov\Exchange\Target\Options\Export;

/**
 * Базовая конфигурация экспорта
 */
class BaseExportOption
{
    /**
     * Путь до хранения файла экспорта
     *
     * @var string
     */
    public string $savePath;

    public function __construct()
    {
        $this->savePath = $_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/export.xml';
    }
}