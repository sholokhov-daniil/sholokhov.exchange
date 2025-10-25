<?php

namespace Sholokhov\Exchange\Repository;

use Exception;
use Sholokhov\Exchange\Repository\Types\Memory;

/**
 * @package Repository
 */
abstract class AbstractRepository implements RepositoryInterface
{
    /**
     * Хранилище данных
     *
     * @var RepositoryInterface[]
     */
    private static array $storage;

    /**
     * Уникальный идентификатор хранилища
     *
     * @var string
     */
    private readonly string $id;

    /**
     * Конфигурация хранилища
     *
     * @var Memory
     */
    private Memory $options;

    /**
     * Генерация уникального идентификатора хранилища
     *
     * @return string
     */
    abstract protected function generateId(): string;

    /**
     * Запрос на получение данных
     *
     * @param array $parameters Параметры запроса
     * @return array
     */
    abstract protected function query(array $parameters = []): array;

    /**
     * Подгрузить данные по идентификатору
     *
     * @param string $id
     * @return mixed
     */
    abstract protected function search(string $id): mixed;

    /**
     * Проверка валидности конфигураций.
     *
     * Если конфигурации не валидны, то необходимо вызвать исключение
     *
     * @extends Exception
     * @param array $options Валидируемая конфигурация хранилища
     * @return void
     */
    abstract protected function checkOptions(array $options): void;

    /**
     * @param array $options Конфигурация хранилища
     */
    public function __construct(array $options)
    {
        $this->checkOptions($options);
        $this->options = new Memory($this->normalizeOptions($options));
        $this->id = $this->generateId();
    }

    /**
     * Идентификатор хранилища (уникальный ID)
     *
     * @final
     * @return string
     */
    final public function getId(): string
    {
        return $this->id;
    }

    /**
     * Получение информации в формате ответа
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->getStorage()->toArray();
    }

    /**
     * Получения информации по идентификатору
     *
     * @param string $id Ключ хранения значения
     * @param mixed|null $default Значение по умолчанию
     * @return mixed
     */
    public function get(string $id, mixed $default = null): mixed
    {
        if ($this->has($id)) {
            return $this->getStorage()->get($id);
        }

        $value = $this->search($id) ?: $default;
        $this->set($id, $value);

        return $value;
    }

    /**
     * Установка значения
     *
     * @param string $id
     * @param mixed $value
     * @return void
     */
    public function set(string $id, mixed $value): void
    {
        $this->getStorage()->set($id, $value);
    }

    /**
     * @param string $id Ключ хранения
     * @return bool
     */
    public function has(string $id): bool
    {
        return $this->getStorage()->has($id);
    }

    /**
     * Удаления значения из хранилища по ключу
     *
     * @param string $id
     * @return void
     */
    public function delete(string $id): void
    {
        $this->getStorage()->delete($id);
    }

    /**
     * Очистка хранилища
     *
     * @return void
     */
    public function clear(): void
    {
        $this->getStorage()->clear();
    }

    /**
     * Полная очистка хранилища
     *
     * @return void
     */
    public function clearAll(): void
    {
        self::$storage = [];
    }

    /**
     * Нормализация(обработка) конфигураций хранилища
     *
     * Метод был создан для возможности переопределения
     *
     * @param array $options
     * @return array
     */
    protected function normalizeOptions(array $options): array
    {
        return $options;
    }

    /**
     * Получение конфигурации
     *
     * @return RepositoryInterface
     */
    protected function getOptions(): RepositoryInterface
    {
        return $this->options;
    }

    /**
     * Получения хранилища данных
     *
     * @final
     * @return Memory
     */
    final protected function getStorage(): Memory
    {
        return self::$storage[$this->getId()] ??= new Memory;
    }
}