<?php

namespace Sholokhov\Exchange\Bootstrap;

use Sholokhov\Exchange\Source;
use Sholokhov\Exchange\Facade\SourceContainer;

/**
 * @internal
 */
class SourceBootstrap implements BootstrapInterface
{
    /**
     * @return void
     */
    public function bootstrap(): void
    {
        SourceContainer
            ::bind('simpleXml', Source\SimpleXml::class)
            ->bind('DBXml', Source\Xml::class)
            ->bind('jsonFile', Source\JsonFile::class)
            ->bind('json', Source\Json::class)
            ->bind('csv', Source\Csv::class)
            ->bind('iBlockElement', Source\Entities\IBlock\Element::class);
    }
}