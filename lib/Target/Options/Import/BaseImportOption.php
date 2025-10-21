<?php

namespace Sholokhov\Exchange\Target\Options\Import;

class BaseImportOption
{
    /**
     * Идентификатор обмена
     *
     * @var string
     */
    public string $hash = '';

    /**
     * Деактивировать все элементы, которые не пришли в импорте
     *
     * @var bool
     */
    public bool $deactivate = false;
}