<?php

namespace Sholokhov\Exchange\Validators\IBlock\Property;

use Sholokhov\Exchange\Messages\ResultInterface;
use Sholokhov\Exchange\Messages\Type\Error;
use Sholokhov\Exchange\Messages\Type\Result;
use Sholokhov\Exchange\Target\Import\IBlock\Property\PropertyEnumeration;
use Sholokhov\Exchange\Validators\ValidatorInterface;

use Bitrix\Iblock\PropertyTable;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Валидация импорта значений списка
 *
 * @package Validator
 */
class EnumerationPropertyValidator implements ValidatorInterface
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

        $property = $value->getProperty();

        if (!$property) {
            return $result->addError(new Error('Property not found'));
        }

        if ($property['PROPERTY_TYPE'] <> PropertyTable::TYPE_LIST || $property['USER_TYPE']) {
            $result->addError(new Error('Invalid property data type'));
        }

        return $result;
    }
}