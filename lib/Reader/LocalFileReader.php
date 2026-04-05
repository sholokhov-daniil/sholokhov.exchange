<?php

namespace Sholokhov\Exchange\Reader;

use Sholokhov\Exchange\Exception\Reader\ReadException;
use Sholokhov\Exchange\Exception\Reader\AccessDeniedException;
use Sholokhov\Exchange\Exception\Reader\FileNotFountException;

/**
 * Производит чтение из локальных файлов
 *
 * @package Reader
 */
class LocalFileReader implements DataReaderInterface
{
    public function __construct(
        private readonly string $path
    )
    {
    }

    /**
     * Производит чтение локального файла возвращая поток данных
     *
     * @inheritDoc
     * @return resource
     */
    public function read()
    {
        $this->checkFile();

        $resource = fopen($this->path, 'rb');

        if (!is_resource($resource)) {
            throw new ReadException('Unable to open file.');
        }

        return $resource;
    }

    /**
     * Проверка наличия и доступности файла
     *
     * @return void
     * @throws AccessDeniedException
     * @throws FileNotFountException
     */
    private function checkFile(): void
    {
        if (!file_exists($this->path)) {
            throw new FileNotFountException('File "' . $this->path . '" does not exist.');
        }

        if (!is_readable($this->path)) {
            throw new AccessDeniedException('File "' . $this->path . '" is not readable.');
        }
    }
}