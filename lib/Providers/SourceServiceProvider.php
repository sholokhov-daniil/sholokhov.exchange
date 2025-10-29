<?php

namespace Sholokhov\Exchange\Providers;

use Sholokhov\Exchange\Container\Container;
use Sholokhov\Exchange\Source;
use Sholokhov\Exchange\Facade\SourceFacade;
use Sholokhov\Exchange\UI\Configuration\Entity\EntityConfig;
use Sholokhov\Exchange\UI\Configuration\Repository\EntityRepository;

use Bitrix\Main\ObjectNotFoundException;

use Psr\Container\NotFoundExceptionInterface;

/**
 * Регистрация источников данных
 *
 * @internal
 */
class SourceServiceProvider
{
    /**
     * @return void
     * @throws ObjectNotFoundException
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(): void
    {
        SourceFacade::bind('xml.simple', Source\SimpleXml::class);
        SourceFacade::bind('xml.db', Source\Xml::class);
        SourceFacade::bind('json.file', Source\JsonFile::class);
        SourceFacade::bind('json', Source\Json::class);
        SourceFacade::bind('csv', Source\Csv::class);
        SourceFacade::bind('iblock.element', Source\Entities\IBlock\Element::class);

        Container::getInstance()->set('ui.source.repository', new EntityRepository('source', EntityConfig::class));
        // TODO: Добавить событие, для кастомных
    }
}