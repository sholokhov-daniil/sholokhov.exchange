<?php

namespace Sholokhov\Exchange\Target\Catalog;

use Bitrix\Currency\CurrencyTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use CCurrency;
use Exception;
use Sholokhov\Exchange\AbstractExchange;
use Sholokhov\Exchange\Messages\Type\Result;
use Sholokhov\Exchange\Messages\ResultInterface;
use Sholokhov\Exchange\Messages\Type\Error;
use Sholokhov\Exchange\Messages\Type\ExchangeResult;

/**
 * @package Import
 */
class Currency extends AbstractExchange
{
    /**
     * Проверка наличия валюты
     *
     * @param array $item
     * @return bool
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws Exception
     */
    public function exists(array $item): bool
    {
        $keyField = $this->getPrimaryField();

        if ($this->getCache()->has($item[$keyField->getTo()])) {
            return true;
        }

        $currency = CurrencyTable::getRow([
            'filter' => [
                $keyField->getTo() => $item[$keyField->getTo()],
            ],
            'select' => ['ID'],
            'cache' => ['ttl' => 36000]
        ]);

        if ($currency) {
            $this->getCache()->set($item[$keyField->getTo()], (int)$currency['ID']);
            return true;
        }

        return false;
    }

    /**
     * Создание валюты
     *
     * @param array $item
     * @return ResultInterface
     * @throws Exception
     */
    public function add(array $item): Result
    {
        $result = new Result;
        $preparedItem = $this->prepareItem($item);

        // TODO: before event

        if ($id = CCurrency::Add($preparedItem)) {
            $result->setData((int)$id);
            $this->logger?->debug("A currency with an identifier was created: $id");
            $this->getCache()->set($item[$this->getPrimaryField()->getTo()], (int)$id);
        } else {
            $result->addError(new Error('Failed to create currency', 500, $preparedItem));
        }

        // TODO: Implement add() method.

        return $result;
    }

    public function update(array $item): Result
    {
        // TODO: Implement update() method.
    }

    /**
     * Преобразование импортируемого значения
     *
     * @param array $item
     * @return array
     */
    private function prepareItem(array $item): array
    {
        if (!isset($item['LANG']['LID'])) {
            $item['LANG']['LID'] = $this->getSiteID();
        }

        if (!isset($item['LANG']['FORMAT_STRING'])) {
            $item['LANG']['FORMAT_STRING'] = $this->getOptions()->get('format_string');
        }

        if (!isset($item['LANG']['FULL_NAME'])) {
            $item['LANG']['FULL_NAME'] = $this->getOptions()->get('full_name');
        }

        return $item;
    }

    /**
     * Проверка загрузки модулей
     *
     * @return ResultInterface
     * @throws LoaderException
     */
    private function checkModules(): ResultInterface
    {
        throw new Exception('In development');
        $result = new Result;

        if (!Loader::includeModule('currency')) {
            $result->addError(new Error('Module "currency" not installed'));
        }

        return $result;
    }
}