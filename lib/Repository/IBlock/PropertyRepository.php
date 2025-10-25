<?php

namespace Sholokhov\Exchange\Repository\IBlock;

use CIBlock;

use Sholokhov\Exchange\Repository\Fields\AbstractFieldRepository;

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;


/**
 * Хранилище информации свойств информационного блока.
 *
 * @final
 * @package Repository
 */
final class PropertyRepository extends AbstractFieldRepository
{
    /**
     * Получение идентификатора информационного блока с которым идет работа
     *
     * @return int
     */
    public function getIBlockID(): int
    {
        return $this->getOptions()->get('iblock_id');
    }

    /**
     * Обработка конфигурационных параметров
     *
     * @param array $options
     * @return array
     */
    protected function normalizeOptions(array $options): array
    {
        $options['iblock_id'] = (int)$options['iblock_id'];
        return $options;
    }

    /**
     * Проверка настроек хранилища
     *
     * @param array $options
     * @return void
     */
    protected function checkOptions(array $options): void
    {
        if (!is_numeric($options['iblock_id']) || !max($options['iblock_id'], 0)) {
            throw new \InvalidArgumentException('iblock_id must be a numeric value');
        }
    }

    /**
     * Выполняет поиск свойств согласно передаваемым параметрам
     *
     * @param array{filter: array, order: array} $parameters Параметры на основе которых формируется запрос
     * @return array
     * @throws LoaderException
     */
    protected function query(array $parameters = []): array
    {
        if (!Loader::includeModule('iblock')) {
            throw new LoaderException('iblock module is not installed');
        }

        $result = [];

        if (!is_array($parameters['order'])) {
            $parameters['order'] = [];
        }

        $parameters['filter'] = array_merge(['ACTIVE' => 'Y'], $parameters['filter'] ?: []);
        $iterator = CIBlock::GetProperties($this->getIBlockID(), $parameters['order'], $parameters['filter']);

        while ($property = $iterator->Fetch()) {
            $result[$property['CODE']] = $property;
        }

        return $result;
    }

    /**
     * Поиск свойства по коду
     *
     * @param string $id
     * @return array|null
     * @throws LoaderException
     */
    protected function search(string $id): ?array
    {
        $iterator = $this->query([
            'filter' => ['CODE' => $id]
        ]);

        return reset($iterator) ?: null;
    }

    /**
     * Получение идентификатора хранилища
     *
     * @return string
     */
    protected function generateId(): string
    {
        return self::class . '_' . $this->getIBlockID();
    }
}