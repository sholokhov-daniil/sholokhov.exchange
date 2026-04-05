<?php

namespace Sholokhov\Exchange\Result\DB;

use CAllDBResult;
use Bitrix\Main\DB\Result;

class OldResultAdapter extends Result
{
    protected CAllDBResult $result;

    public function __construct(CAllDBResult $result)
    {
        $this->result = $result;
        parent::__construct($result->result);
    }

    public function getFields()
    {
        // TODO: пу-пу
        return null;
    }

    public function getSelectedRowsCount(): int
    {
        return (int)$this->result->SelectedRowsCount();
    }

    protected function fetchRowInternal(): array|false
    {
        return $this->result->Fetch();
    }
}