<?php

namespace Sholokhov\Exchange\Providers;

use Sholokhov\Exchange\Source;
use Sholokhov\Exchange\Facade\SourceFacade;

/**
 * Регистрация источников данных
 *
 * @internal
 */
class SourceServiceProvider
{
    /**
     * @return void
     */
    public function __invoke(): void
    {
        SourceFacade::bind('xml.simple', Source\SimpleXml::class);
        SourceFacade::bind('xml.db', Source\Xml::class);
        SourceFacade::bind('json.file', Source\JsonFile::class);
        SourceFacade::bind('json', Source\Json::class);
        SourceFacade::bind('csv', Source\Csv::class);
        SourceFacade::bind('iblock.element', Source\Entities\IBlock\Element::class);

        // TODO: Добавить событие, для кастомных
    }
}