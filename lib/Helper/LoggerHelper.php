<?php

namespace Sholokhov\Exchange\Helper;

use Throwable;

/**
 * @package Helper
 */
class LoggerHelper
{
    /**
     * Преобразование Exception в строку.
     *
     * @param Throwable $throwable
     * @return string
     */
    public static function exceptionToString(Throwable $throwable): string
    {
        $trace = [];

        foreach ($throwable->getTrace() as $value) {
            if (!is_array($value['args'])) {
                $value['args'] = [];
            }

            foreach ($value['args'] as &$argument) {
                if (is_object($argument)) {
                    $argument = sprintf('Object(%s)', $argument::class);
                }
            }

            $trace[] = $value;
        }

        return json_encode(
            [
                'message' => $throwable->getMessage(),
                'code' => $throwable->getCode(),
                'trace' => $trace
            ],
            JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES
        );
    }

}