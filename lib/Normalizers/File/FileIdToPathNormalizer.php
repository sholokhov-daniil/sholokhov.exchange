<?php

namespace Sholokhov\Exchange\Normalizers\File;

use Bitrix\Main\Diag\Debug;
use CFile;
use Throwable;

use Sholokhov\Exchange\Fields\FieldInterface;
use Sholokhov\Exchange\Normalizers\NormalizerInterface;

use Bitrix\Main\SiteTable;

/**
 * Преобразует ID файла в путь
 */
class FileIdToPathNormalizer implements NormalizerInterface
{
    private readonly string $siteId;
    private readonly string $protocol;
    private static array $cache = [];

    public function __construct(string $siteId, string $protocol = 'https')
    {
        $this->siteId = $siteId;
        $this->protocol = $protocol;
    }

    /**
     * Преобразование значения
     *
     * @param mixed $value
     * @param FieldInterface $field
     * @return array|string
     */
    public function normalize(mixed $value, FieldInterface $field): array|string
    {
        if (is_array($value)) {
            return array_unique(
                array_filter(
                    array_map($this->getUrl(...), $value)
                )
            );
        } elseif (is_numeric($value)) {
            return $this->getUrl($value);
        }

        return '';
    }

    /**
     * Формирование пути до изображения
     *
     * @param int $fileId
     * @return string
     */
    protected function getUrl(int $fileId): string
    {
        $path = CFile::GetPath($fileId);
        return $path ? $this->getHost() . $path : '';
    }

    /**
     * Хост изображения
     *
     * @return string
     */
    protected function getHost(): string
    {
        if (!array_key_exists($this->siteId, self::$cache)) {
            try {
                $site = SiteTable::getRow([
                    'filter' => ['LID' => $this->siteId],
                    'select' => ['SERVER_NAME'],
                    'cache' => ['ttl' => 3600000]
                ]);

                $host = (string)($site['SERVER_NAME'] ?? '');
            } catch (Throwable) {
                $host = '';
            }
            self::$cache[$this->siteId] = rtrim($host, '/');
        }

        return $this->protocol . '://' . self::$cache[$this->siteId];
    }
}