<?php

namespace Sholokhov\Exchange\Validators\UserFields;

use Sholokhov\Exchange\Messages\ResultInterface;
use Sholokhov\Exchange\Messages\Type\Error;
use Sholokhov\Exchange\Messages\Type\Result;
use Sholokhov\Exchange\Target\Import\UserFields\ExchangeUserFieldInterface;
use Sholokhov\Exchange\Validators\ValidatorInterface;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Проверка наличия кода свойства в который идет обмен
 *
 * @package Validator
 */
class PropertyCodeValidate implements ValidatorInterface
{
    /**
     * Валидация обмена
     *
     * @param mixed $value
     * @return ResultInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function validate(mixed $value): ResultInterface
    {
        $result = new Result;

        if (!($value instanceof ExchangeUserFieldInterface)) {
            return $result->addError(new Error('Exchange not implement ' . ExchangeUserFieldInterface::class));
        }

        if (!$value->getPropertyCode()) {
            return $result->addError(new Error('Property code is required'));
        }

        return $result;
    }
}