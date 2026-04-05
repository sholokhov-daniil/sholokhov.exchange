<?php

namespace Sholokhov\Exchange\Exception\Reader;

use Throwable;

use Sholokhov\Exchange\Reader\DataReaderInterface;

/**
 * Исключение, выбрасываемое при отсутствии прав доступа к источнику данных.
 *
 * Используется во всех реализациях {@see DataReaderInterface},
 * когда источник данных существует, но недоступен для чтения из-за:
 * - прав файловой системы (локальные файлы)
 * - ограничений доступа (HTTP/FTP/SSH)
 * - других механизмов авторизации/ограничений
 *
 * Это исключение наследуется от {@see ReaderException}, поэтому его
 * можно ловить как специфически, так и через базовое исключение для всех ошибок чтения.
 *
 * @package Reader
 */
class AccessDeniedException extends ReaderException
{
    public function __construct(string $message = 'Access denied', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}