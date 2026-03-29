<?php

namespace Sholokhov\Exchange\Exception\Reader;

use Sholokhov\Exchange\Reader\DataReaderInterface;

/**
 * Исключение, выбрасываемое при отсутствии файла или ресурса с данными.
 *
 * Используется во всех реализациях {@see DataReaderInterface},
 * когда ожидаемый источник данных не существует или не найден:
 * - локальный файл отсутствует на диске
 * - ресурс на FTP/HTTP/SSH не найден
 *
 * Это исключение наследуется от {@see ReaderException}, поэтому его
 * можно ловить как специфически, так и через базовое исключение для всех reader-ошибок.
 */
class FileNotFountException extends ReaderException
{
}