<?php

namespace Sholokhov\Exchange\Helper;

/**
 * @package Helper
 */
class IO
{
    /**
     * Получение содержимого файла
     *
     * @param string $path
     * @return string
     */
    public static function getFileContent(string $path): string
    {
        $contents = '';

        $resource = fopen($path, 'rb');
        if (!$resource) {
            return $contents;
        }

        while (!feof($resource)) {
            $contents .= fread($resource, 8192);
        }

        fclose($resource);

        return $contents;
    }
}