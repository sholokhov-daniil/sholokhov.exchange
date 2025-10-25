<?php

namespace Sholokhov\Exchange\Repository\Map;

use Exception;
use LogicException;
use InvalidArgumentException;

use Sholokhov\Exchange\Fields\FieldInterface;
use Sholokhov\Exchange\Validators\ValidatorInterface;

/**
 * Хранилище карты обмена
 *
 * @package Repository
 */
class MappingRegistry implements MappingRegistryInterface
{
    /**
     * Карта обмена
     *
     * @var array
     */
    private array $map = [];

    /**
     * Ключевое поле обмена
     *
     * @var FieldInterface|null
     */
    private ?FieldInterface $primaryField = null;

    /**
     * Поле хранящее хеш импорта
     *
     * @var FieldInterface|null
     */
    private ?FieldInterface $hashField= null;

    /**
     * Валидация карты обмена
     *
     * @var ValidatorInterface|null
     */
    private ?ValidatorInterface $validator;

    /**
     * @param ValidatorInterface|null $validator Валидатор карты обмена
     */
    public function __construct(ValidatorInterface $validator = null)
    {
        $this->validator = $validator;
    }

    /**
     * Установка карты обмена
     *
     * @param FieldInterface[] $map
     * @return MappingRegistry
     * @throws Exception
     */
    public function setFields(array $map): static
    {
        $this->validate($map);
        $this->resetFields();

        foreach ($map as $field) {
            $this->assertFieldType($field);
            $this->processField($field);
        }

        $this->assertPrimaryExists();
        $this->map = $map;

        return $this;
    }

    /**
     * Получение карты обмена
     *
     * @return FieldInterface[]
     */
    public function getFields(): array
    {
        return $this->map;
    }

    /**
     * Получение свойства отвечающего за идентификацию значения
     *
     * @return FieldInterface|null
     */
    public function getPrimaryField(): ?FieldInterface
    {
        return $this->primaryField;
    }

    /**
     * Получение поля отвечающего за хеш обмена
     *
     * @return FieldInterface|null
     */
    public function getHashField(): ?FieldInterface
    {
        return $this->hashField;
    }

    /**
     * @param array $fields
     * @return void
     * @throws Exception
     */
    private function validate(array $fields): void
    {
        if ($this->validator) {
            $result = $this->validator->validate($fields);
            if (!$result->isSuccess()) {
                throw new Exception(implode(PHP_EOL, $result->getErrorMessages()));
            }
        }
    }

    /**
     * Проверка корректности типа поля
     *
     * @param mixed $field
     * @return void
     */
    private function assertFieldType(mixed $field): void
    {
        if (!$field instanceof FieldInterface) {
            throw new InvalidArgumentException('Field must implement ' . FieldInterface::class);
        }
    }

    /**
     * Обработка свойства
     *
     * @param FieldInterface $field
     * @return void
     */
    private function processField(FieldInterface $field): void
    {
        if ($field->isPrimary()) {
            $this->assignUniqueField($this->primaryField, $field, 'Primary key already exists');
        }

        if ($field->isHash()) {
            $this->assignUniqueField($this->hashField, $field, 'Hash key already exists');
        }
    }

    /**
     * Проверка уникальности поля
     *
     * @param FieldInterface|null $slot
     * @param FieldInterface $field
     * @param string $errorMessage
     * @return void
     */
    private function assignUniqueField(?FieldInterface &$slot, FieldInterface $field, string $errorMessage): void
    {
        if ($slot !== null) {
            throw new LogicException($errorMessage);
        }
        $slot = $field;
    }

    /**
     * Проверка наличия ключевого поля
     *
     * @return void
     */
    private function assertPrimaryExists(): void
    {
        if ($this->primaryField === null) {
            throw new LogicException("No primary key field found");
        }
    }

    /**
     * Очистка данных карты
     *
     * @return void
     */
    private function resetFields(): void
    {
        $this->primaryField = null;
        $this->hashField = null;
    }
}