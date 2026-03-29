<?php

namespace Sholokhov\Exchange\Exception\Reader;

use Sholokhov\Exchange\Exception\ExchangeException;
use Sholokhov\Exchange\Reader\DataReaderInterface;

/**
 * Базовое исключение для всех ошибок работы с источниками данных (Reader).
 *
 * Используется во всех реализациях {@see DataReaderInterface}.
 *
 * Все специфические ошибки чтения (например, {@see FileNotFoundException},
 * {@see AccessDeniedException}, {@see ReadException}) наследуются от этого класса,
 * что позволяет:
 * - ловить все reader-ошибки через один тип
 * - обрабатывать специфические ошибки по отдельности
 *
 * @package Reader
 */
class ReaderException extends ExchangeException
{
}