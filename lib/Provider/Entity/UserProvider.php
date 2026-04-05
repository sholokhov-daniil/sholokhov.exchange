<?php

namespace Sholokhov\Exchange\Provider\Entity;

use CUser;

use Sholokhov\Exchange\Converter\DbResultConverter;

use Bitrix\Main\DB\Result;

/**
 * Источник данных пользователей Bitrix
 *
 * Реализует ленивую итерацию по пользователям с использованием курсорной пагинации.
 * Данные загружаются батчами (пакетами) с использованием фильтрации по ID (>ID)
 * и ограничения количества записей через nTopCount.
 *
 * Особенности:
 * - Использует курсорную пагинацию (по ID), что гарантирует отсутствие дубликатов и пропусков
 * - Не использует OFFSET и постраничную навигацию
 * - Подходит для обработки больших объемов данных
 * - Возвращает "сырые" данные пользователей из Bitrix (без трансформаций)
 *
 * @package Provider
 */
class UserProvider implements EntityProviderInterface
{
    use ProviderSelectionTrait;

    /**
     * Список получаемых UF полей
     *
     * @var array
     */
    protected array $userFields = [];

    /**
     * Выполняет запрос к элементам ИБ с текущей конфигурацией фильтра, сортировки, выборки и навигации.
     *
     * @return Result|null
     */
    public function query(): ?Result
    {
        $result = CUser::GetList(
            ['ID' => 'ASC'],
            '',
            $this->filter,
            [
                'SELECT' => $this->userFields,
                'FIELDS' => $this->buildSelect(),
                'NAV_PARAMS' => [
                    'nTopCount' => $this->limit,
                ]
            ]
        ) ?: null;

        return $result ? DbResultConverter::fromOld($result) : null;
    }

    /**
     * Установка UF свойств для выборки
     *
     * @param array $userFields
     * @return $this
     */
    public function setUserFields(array $userFields): static
    {
        $this->userFields = $userFields;
        return $this;
    }

    /**
     * Формирует список полей для выборки
     *
     * Исключает чувствительные поля:
     * - PASSWORD
     * - PASSWORD_HASH
     *
     * Гарантирует наличие поля ID в выборке.
     *
     * @return string[]
     */
    protected function buildSelect(): array
    {
        $select = array_filter(
            $this->select,
            static fn($val) => !in_array($val, ['PASSWORD', 'PASSWORD_HASH'])
        );

        if (!in_array('ID', $select)) {
            $select[] = 'ID';
        }

        return $select;
    }
}