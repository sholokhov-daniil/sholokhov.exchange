<?php

namespace Sholokhov\Exchange\Repository\IBlock;

use _CIBElement;
use CIBlockElement;

use Sholokhov\Exchange\Repository\AbstractRepository;

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;

/**
 * @package Repository
 *
 * @method _CIBElement|null|mixed get(string $id)
 */
class ElementRepository extends AbstractRepository
{
    /**
     * Идентификатор информационного блока по которому производится поиск
     *
     * @return int
     */
    public function getIBlockID(): int
    {
        return $this->getOptions()->get('iblock_id');
    }

    /**
     * Ключ по которому будет производиться проверка уникальности
     *
     * @return string
     */
    public function getPrimary(): string
    {
        return $this->getOptions()->get('primary');
    }

    /**
     * Нормализация конфигурации хранилища
     *
     * @param array $options
     * @return array
     */
    protected function normalizeOptions(array $options): array
    {
        $options['iblock_id'] = (int)$options['iblock_id'];
        $options['primary'] = (string)($options['primary'] ?: 'XML_ID');

        return $options;
    }

    /**
     * Проверка валидности конфигурации хранилища
     *
     * @param array $options Конфигурация
     * @return void
     * @throws LoaderException
     */
    protected function checkOptions(array $options): void
    {
        if (!isset($options['iblock_id']) || !is_numeric($options['iblock_id'])) {
            throw new LoaderException('"iblock_id" is required!');
        }
    }

    /**
     * Поиск элемента
     *
     * @param array $parameters
     * @return _CIBElement[]
     * @throws LoaderException
     */
    protected function query(array $parameters = []): array
    {
        $result = [];

        if (!Loader::includeModule('iblock')) {
            throw new LoaderException('Module "iblock" not installed');
        }

        $filter = ['=IBLOCK_ID' => $this->getIBlockID()];
        $select = ['ID', 'IBLOCK_ID', 'NAME', 'CODE', 'XML_ID'];

        $parameters['filter'] = array_merge($parameters['filter'] ?? [], $filter);
        $parameters['select'] = array_merge($parameters['select'] ?? [], $select);

        if (!isset($parameters['order']) || !is_array($parameters['order'])) {
            $parameters['order'] = [];
        }

        $iterator = CIBlockElement::GetList($parameters['order'], $parameters['filter'], false, false, $parameters['select']);

        while ($element = $iterator->GetNextElement()) {
            $result[] = $element;
        }

        return $result;
    }

    /**
     * Поиск элемента по идентификатору {@see static::getPrimary()}
     *
     * @param string $id
     * @return _CIBElement|null
     * @throws LoaderException
     */
    protected function search(string $id): ?_CIBElement
    {
        $iterator = $this->query([
            'filter' => [$this->getPrimary() => $id]
        ]);

        return reset($iterator) ?: null;
    }

    /**
     * Идентификатор хранилища
     *
     * @return string
     */
    protected function generateId(): string
    {
        return static::class . '_' . $this->getIBlockID() . '_' . $this->getPrimary();
    }
}