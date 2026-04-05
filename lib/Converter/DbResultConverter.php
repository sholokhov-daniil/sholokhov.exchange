<?php

namespace Sholokhov\Exchange\Converter;

use Bitrix\Main\DB\Result;
use CAllDBResult;
use Sholokhov\Exchange\Result\DB\OldResultAdapter;

class DbResultConverter
{
    /**
     * Преобразовывает старый формат результата запроса к БД в новый
     *
     * @param CAllDBResult $result
     * @return Result
     */
    public static function fromOld(CAllDBResult $result): Result
    {
        return new OldResultAdapter($result);
    }
}