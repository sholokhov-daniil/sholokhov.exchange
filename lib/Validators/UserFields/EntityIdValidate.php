<?php

namespace Sholokhov\Exchange\Validators\UserFields;

use Sholokhov\Exchange\Messages\ResultInterface;
use Sholokhov\Exchange\Messages\Type\Error;
use Sholokhov\Exchange\Messages\Type\Result;
use Sholokhov\Exchange\Target\Import\UserFields\ExchangeUserFieldInterface;
use Sholokhov\Exchange\Validators\ValidatorInterface;

/**
 * Проверка наличия ID сущности
 *
 * @package Validator
 */
class EntityIdValidate implements ValidatorInterface
{
    /**
     * Валидация обмена
     *
     * @param mixed $value
     * @return ResultInterface
     */
    public function validate(mixed $value): ResultInterface
    {
        $result = new Result;

        if (!($value instanceof ExchangeUserFieldInterface)) {
            return $result->addError(new Error('Exchange not implement ' . ExchangeUserFieldInterface::class));
        }

        if (!$value->getEntityId()) {
            return $result->addError(new Error('EntityId not set'));
        }

        return $result;
    }
}