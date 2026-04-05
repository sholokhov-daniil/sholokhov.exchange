<?php

namespace Sholokhov\Exchange\Result\DB;

use CIBlockResult as BaseResult;

class CIBlockElementResult extends OldResultAdapter
{
    /**
     * @var callable|null
     */
    protected $normalizer;

    /**
     * @var BaseResult
     */
    protected \CAllDBResult $result;

    public function __construct(BaseResult $result, ?callable $normalizer = null)
    {
        $this->normalizer = $normalizer;
        parent::__construct($result);
    }

    protected function fetchRowInternal(): array|false
    {
        $element = $this->result->GetNextElement();

        if (!$element) {
            return false;
        }

        if (is_callable($this->normalizer)) {
            $item = call_user_func($this->normalizer, $element);
        } else {
            $item = $element->GetFields();
        }

        return $item;
    }
}