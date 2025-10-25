<?php

namespace Sholokhov\Exchange\Validators\IBlock;

use Sholokhov\Exchange\Messages\ResultInterface;
use Sholokhov\Exchange\Messages\Type\Error;
use Sholokhov\Exchange\Messages\Type\Result;

use Sholokhov\Exchange\Target\Import\IBlock\IBlockExchangeInterface;
use Sholokhov\Exchange\Validators\ValidatorInterface;

/**
 * Производит проверку корректности ID инфоблока
 *
 * @package Validator
 */
readonly class IBlockIdValidator implements ValidatorInterface
{
    public function validate(mixed $value): ResultInterface
    {
        $result = new Result;

        if (!($value instanceof IBlockExchangeInterface)) {
            return $result->addError(new Error('Value must be an instance of ' . IBlockExchangeInterface::class));
        }

        if ($value->getIBlockID() <= 0) {
            return $result->addError(new Error('IBlock ID is required'));
        }

        return $result;
    }
}