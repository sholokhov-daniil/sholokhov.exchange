<?php

namespace Sholokhov\Exchange\Validators;

use Sholokhov\Exchange\Messages\ResultInterface;
use Sholokhov\Exchange\Messages\Type\Error;
use Sholokhov\Exchange\Messages\Type\Result;

use Bitrix\Main\Loader;

/**
 * Производит проверку доступности модулей
 *
 * @package Validator
 */
readonly class CheckAvailableModules implements ValidatorInterface
{
    public function __construct(private array $modules)
    {
    }

    public function validate(mixed $value): ResultInterface
    {
        $result = new Result();

        foreach ($this->modules as $module) {
            if (!Loader::includeModule($module)) {
                $result->addError(new Error('Module "' . $module . '" not installed'));
            }
        }

        return $result;
    }
}