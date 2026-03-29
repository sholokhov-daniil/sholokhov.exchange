<?php

namespace Sholokhov\Exchange\Exception\Reader;

use Sholokhov\Exchange\Reader\DataReaderInterface;

/**
 * Исключение, выбрасываемое при ошибках чтения данных из источника.
 *
 * Используется во всех реализациях {@see DataReaderInterface},
 * когда не удаётся получить или открыть поток данных.
 *
 * Примеры причин:
 * - файл недоступен для чтения
 * - ошибка соединения при чтении по HTTP/FTP/SSH
 * - другие непредвиденные ошибки доступа к ресурсу
 *
 * Это исключение наследуется от {@see ReaderException}, поэтому его
 * можно ловить как специфически, так и через базовое исключение для всех reader-ошибок.
 */
class ReadException extends ReaderException
{
}