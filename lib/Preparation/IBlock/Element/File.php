<?php

namespace Sholokhov\Exchange\Preparation\IBlock\Element;

use CFile;

use Sholokhov\Exchange\Preparation\AbstractPrepare;

use Sholokhov\Exchange\Fields\FieldInterface;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerAwareInterface;

/**
 * Преобразование пути до файла в массив формата $_FILES {@see CFile::MakeFileArray()}
 *
 * @package Preparation
 */
class File extends AbstractPrepare implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * Проверка поддержки свойства
     *
     * @param mixed $value Значение, которое необходимо преобразовать
     * @param FieldInterface $field Свойство, которое преобразовывается
     * @return bool
     */
    public function supported(mixed $value, FieldInterface $field): bool
    {
        return in_array($field->getTo(), ['PREVIEW_PICTURE', 'DETAIL_PICTURE']);
    }

    /**
     * Логика преобразования значения
     *
     * @param mixed $value
     * @param FieldInterface $field
     * @return mixed
     */
    protected function logic(mixed $value, FieldInterface $field): array
    {
        return !empty($value) ? CFile::MakeFileArray($value) : [];
    }
}