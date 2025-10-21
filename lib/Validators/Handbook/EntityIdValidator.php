<?php

namespace Sholokhov\Exchange\Validators\Handbook;

use Sholokhov\Exchange\Messages\ResultInterface;
use Sholokhov\Exchange\Messages\Type\Error;
use Sholokhov\Exchange\Messages\Type\Result;
use Sholokhov\Exchange\Target\Import\Highloadblock\Element;
use Sholokhov\Exchange\Validators\ValidatorInterface;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Проверяет наличие ID справочника
 *
 * @package Validator
 */
class EntityIdValidator implements ValidatorInterface
{
    /**
     * Валидация ID
     *
     * @param mixed $value
     * @return ResultInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function validate(mixed $value): ResultInterface
    {
        $result = new Result;

        if (!($value instanceof Element)) {
            return $result->addError(new Error('Value must be an instance of ' . Element::class));
        }

        if ($value->getHlID() <= 0) {
            return $result->addError(new Error('Hl ID must be greater than 0'));
        }

        return $result;
    }
}