<?php

namespace Sholokhov\Exchange\Source\Entities\IBlock;

use Iterator;
use CIBlockResult;

use Sholokhov\Exchange\Provider\Entity\IBlockElementProvider;
use Sholokhov\Exchange\Provider\Entity\IBlockElementProviderInterface;

/**
 * Источник данных основан на элементах информационного блока (IBlock)
 *
 * Поддерживает ленивую загрузку данных по батчам с фильтрацией, выборкой полей
 * и свойств. Реализует интерфейс Iterator для удобной итерации через foreach.
 *
 * Особенности:
 * - Поддержка батчевой загрузки через ID
 * - Возможность выбирать поля и свойства элементов
 * - Минимальное потребление памяти при больших объёмах данных
 *
 * @package Source
 */
class Element implements Iterator
{
    /**
     * Размер пакета данных (батч)
     *
     * @var int
     */
    protected int $limit = 2000;

    /**
     * Последний обработанный ID (для батчевой загрузки)
     *
     * @var int
     */
    protected int $lastId = 0;

    /**
     * Фильтр для выборки элементов ИБ
     *
     * @var array
     */
    protected array $filter = [];

    /**
     * Поля для выборки
     *
     * @var array
     */
    protected array $select = ['ID', 'IBLOCK_ID', 'NAME'];

    /**
     * Свойства элементов, которые нужно загрузить
     *
     * @var array
     */
    protected array $properties = [];

    /**
     * Текущий батч элементов
     *
     * @var array
     */
    protected array $batch = [];

    /**
     * Признак окончания итерации
     *
     * @var bool
     */
    protected bool $finished = false;

    /**
     * Провайдер элементов ИБ
     *
     * @var IBlockElementProviderInterface
     */
    protected readonly IBlockElementProviderInterface $provider;

    /**
     * @param IBlockElementProviderInterface|null $provider Провайдер элементов ИБ
     */
    public function __construct(?IBlockElementProviderInterface $provider = null)
    {
        $this->provider = $provider ?? new IBlockElementProvider;
    }

    /**
     * Устанавливает лимит элементов на один батч
     *
     * @param int $limit
     *
     * @return $this
     */
    public function setLimit(int $limit): static
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Устанавливает ID информационного блока
     *
     * @param int $id ID информационного блока
     *
     * @return $this
     */
    public function setIBlockId(int $id): static
    {
        $this->filter['=IBLOCK_ID'] = $id;
        return $this;
    }

    /**
     * Устанавливает фильтр для выборки элементов
     *
     * @param array $filter Ассоциативный массив фильтра Bitrix
     *
     * @return $this
     */
    public function setFilter(array $filter): static
    {
        $this->filter = $filter;
        $this->rewind();
        return $this;
    }

    /**
     * Устанавливает поля выборки элементов
     *
     * @param array $select Список полей
     *
     * @return $this
     */
    public function setSelect(array $select): static
    {
        $this->select = $select;

        if (!in_array('ID', $this->select)) {
            $this->select[] = 'ID';
        }

        return $this;
    }

    /**
     * Устанавливает свойства элементов для выборки
     *
     * @param array $properties Массив кодов свойств
     *
     * @return $this
     */
    public function setProperties(array $properties): static
    {
        $this->properties = $properties;
        return $this;
    }

    /**
     * Возвращает текущий элемент итератора
     *
     * @return mixed
     */
    public function current(): mixed
    {
        return current($this->batch);
    }

    /**
     * Переводит итератор на следующий элемент
     *
     * Если текущий батч закончился, подгружает следующий батч.
     *
     * @return void
     */
    public function next(): void
    {
        next($this->batch);

        if (!$this->valid()) {
            $this->fetchBatch();
        }
    }

    /**
     * Возвращает ключ текущего элемента итератора
     *
     * @return mixed
     */
    public function key(): mixed
    {
        return key($this->batch);
    }

    /**
     * Проверяет валидность текущего элемента
     *
     * @return bool true, если элемент существует, false — если батч закончился
     */
    public function valid(): bool
    {
        $key = $this->key();
        return $key !== null && $key !== false;
    }

    /**
     * Сбрасывает итератор
     *
     * Перезапускает итерацию с самого начала
     *
     * @return void
     */
    public function rewind(): void
    {
        $this->lastId = 0;
        $this->finished = false;
        $this->batch = [];
        $this->fetchBatch();
    }

    /**
     * Загружает следующий батч элементов из инфоблока
     *
     * - Поддерживает фильтрацию по последнему ID
     * - Загружает поля и свойства
     * - Обновляет состояние итератора
     *
     * @return void
     */
    protected function fetchBatch(): void
    {
        if ($this->finished) {
            return;
        }

        $filter = $this->buildFilter();
        $result = $this->executeQuery($filter);

        $this->batch = [];

        if (is_null($result)) {
            $this->finished = true;
            return;
        }

        $this->processResult($result);
    }

    /**
     * Строит фильтр для запроса к элементам ИБ
     *
     * Добавляет условие по последнему ID для батчевой загрузки.
     *
     * @return array
     */
    protected function buildFilter(): array
    {
        return array_merge(
            $this->filter,
            ['>ID' => $this->lastId]
        );
    }

    /**
     * Выполняет запрос к провайдеру элементов ИБ
     *
     * @param array $filter
     *
     * @return CIBlockResult|null
     */
    protected function executeQuery(array $filter): ?CIBlockResult
    {
        return $this->provider
            ->setFilter($filter)
            ->setOrder(['ID' => 'ASC'])
            ->setSelect($this->select)
            ->setLimit($this->limit)
            ->query();
    }

    /**
     * Обрабатывает результат запроса и формирует батч
     *
     * @param CIBlockResult $result
     *
     * @return void
     */
    protected function processResult(CIBlockResult $result): void
    {
        while ($element = $result->GetNextElement()) {
            $item = $element->GetFields();
            $item['PROPERTIES'] = [];

            $this->lastId = $item['ID'];

            if (!empty($this->properties)) {
                $properties = $element->GetProperties();

                foreach ($this->properties as $code) {
                    if (!empty($properties[$code])) {
                        $item['PROPERTIES'][$code] = $properties[$code];
                    }
                }
            }

            $this->batch[] = $item;
        }
    }
}