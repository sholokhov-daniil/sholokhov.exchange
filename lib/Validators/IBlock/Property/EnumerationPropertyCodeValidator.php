<?php

namespace Sholokhov\Exchange\Validators\IBlock\Property;

use Sholokhov\Exchange\Messages\ResultInterface;
use Sholokhov\Exchange\Messages\Type\Error;
use Sholokhov\Exchange\Messages\Type\Result;
use Sholokhov\Exchange\Target\Import\IBlock\Property\PropertyEnumeration;
use Sholokhov\Exchange\Validators\ValidatorInterface;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Проверка наличия кода свойства в который должен проводиться импорт
 *
 * @package Validator
 */
class EnumerationPropertyCodeValidator implements ValidatorInterface
{

    /**
     * Валидация импорта
     *
     * @param mixed $value
     * @return ResultInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function validate(mixed $value): ResultInterface
    {
        $result = new Result;

        if (!($value instanceof PropertyEnumeration)) {
            return $result->addError(new Error('Value must be an instance of ' . PropertyEnumeration::class));
        }

        if (!$value->getPropertyCode()) {
            return $result->addError(new Error('Property code is required.'));
        }

        return $result;
    }
}