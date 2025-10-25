<?php

namespace Sholokhov\Exchange\Bootstrap;

/**
 * Производит инициализацию модуля
 *
 * @internal
 */
interface BootstrapInterface
{
    /**
     * Запуск загрузки
     *
     * @return void
     */
    public function bootstrap(): void;
}