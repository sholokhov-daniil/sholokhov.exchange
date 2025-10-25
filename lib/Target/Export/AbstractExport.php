<?php

namespace Sholokhov\Exchange\Target\Export;

use CTempFile;
use Exception;

use Sholokhov\Exchange\AbstractExchange;

use Bitrix\Main\IO\File;

/**
 * Базовый функционал экспорта
 */
abstract class AbstractExport extends AbstractExchange
{
    /**
     * Временный файл хранения экспорта
     *
     * @var File|null
     */
    private File|null $tmp = null;

    public function __destruct()
    {
        $this->deleteTmp();
    }

    /**
     * Получение Tmp файла
     *
     * Если tmp файл отсутствует, то он создается
     *
     * @return File|null
     * @throws Exception
     */
    protected function getTmp(): ?File
    {
        if (!$this->tmp) {
            $this->initializeTmp();
        }

        return $this->tmp;
    }

    /**
     * Очистка tmp файла
     *
     * @return void
     */
    final protected function deleteTmp(): void
    {
        $this->tmp?->delete();
        $this->tmp = null;
    }

    /**
     * Инициализация tmp файла
     *
     * @return void
     * @throws Exception
     */
    protected function initializeTmp(): void
    {
        if ($this->tmp) {
            $this->deleteTmp();
        }

        $fileName = rtrim(CTempFile::GetFileName(), '/');
        $file = new File($fileName);

        if ($file->putContents('') === false) {
            throw new Exception("Can't write to file \"$fileName\"");
        }

        $this->tmp = $file;
    }
}