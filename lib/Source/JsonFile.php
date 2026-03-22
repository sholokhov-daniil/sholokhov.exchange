<?php

namespace Sholokhov\Exchange\Source;

use Sholokhov\Exchange\Helper\IO;
use Sholokhov\Exchange\Exception\Source\InvalidJsonFileException;

/**
 * Источник данных на основе json файла
 *
 * @package Source
 */
class JsonFile extends Json
{
    /**
     * @param string $path Место размещения json файла (локально или удаленно)
     * @param array $options
     * @throws InvalidJsonFileException
     */
    public function __construct(string $path, array $options = [], ?callable $loader = null)
    {
        $loader ??= IO::getFileContent(...);
        $content = call_user_func($loader, $path);

        if (!is_string($content)) {
            throw new InvalidJsonFileException('Invalid json file');
        }

        parent::__construct($content, $options);
    }
}