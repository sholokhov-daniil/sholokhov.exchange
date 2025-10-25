<?php

namespace Sholokhov\Exchange\Validators;

use TypeError;

use Sholokhov\Exchange\Messages\Type\Error;
use Sholokhov\Exchange\Messages\Type\Result;
use Sholokhov\Exchange\Fields\FieldInterface;
use Sholokhov\Exchange\Messages\ResultInterface;

/**
 * Проверка стандартной карты обмена
 *
 * @package Validator
 */
class MapValidator implements ValidatorInterface
{
    /**
     * Валидация карты обмена
     *
     * @param mixed $value
     * @return ResultInterface
     */
    public function validate(mixed $value): ResultInterface
    {
        if (!is_array($value)) {
            throw new TypeError("Value must be an array");
        }

        $primary = false;
        $result = new Result;

        foreach ($value as $field) {
            if (!($field instanceof FieldInterface)) {
                $result->addError(new Error('Incorrect field description', 400, $field));
                break;
            }

            if ($field->isPrimary()) {
                if ($primary) {
                    $result->addError(new Error('Duplication of the identification', 400, $field));
                } else {
                    $primary = true;
                }
            }

            if ($field->getFrom() === '' && !$field->isHash()) {
                $result->addError(new Error('Field path is required', 400, $field));
            }
        }

        if (!$primary) {
            $result->addError(new Error('No identification field', 400));
        }

        return $result;
    }
}