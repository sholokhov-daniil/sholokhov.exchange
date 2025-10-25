<?php

namespace Sholokhov\Exchange\Factory\Result;

use Sholokhov\Exchange\Repository\Result\SimpleResultRepository;

class SimpleFactory
{
    public function __invoke(): SimpleResultRepository
    {
        return new SimpleResultRepository;
    }
}