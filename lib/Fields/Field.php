<?php

namespace Sholokhov\Exchange\Fields;

use Sholokhov\Exchange\Repository\Types\Memory;
use Sholokhov\Exchange\Repository\RepositoryInterface;

/**
 * Описание структуры и логики работы со свойством
 *
 * @package Field
 */
class Field implements FieldInterface
{
    /**
     * Конфигурация свойства
     *
     * @var RepositoryInterface
     */
    private readonly RepositoryInterface $container;

    /**
     * Является идентификационным полем
     *
     * @return bool
     */
    public function isPrimary(): bool
    {
        return $this->getContainer()->get('key_field', false);
    }

    /**
     * Поле хранит хэш импорта
     *
     * @return bool
     */
    public function isHash(): bool
    {
        return $this->getContainer()->get('hash_field', false);
    }

    /**
     * При отсутствии сущности попытается создать
     *
     * @return bool
     */
    public function isCreatedLink(): bool
    {
        return $this->getContainer()->get('is_created_link', true);
    }

    /**
     * Установить флаг идентификационного поля
     *
     * @param bool $value
     * @return $this
     */
    public function setPrimary(bool $value = true): self
    {
        $this->getContainer()->set('key_field', $value);
        return $this;
    }

    /**
     * Установка флага, что поле хранит хэш импорта
     *
     * @param bool $value
     * @return $this
     */
    public function setHash(bool $value = true): self
    {
        $this->getContainer()->set('hash_field', $value);
        return $this;
    }

    /**
     * При отсутствии сущности попытается создать
     *
     * @param bool $value
     * @return $this
     */
    public function setCreatedLink(bool $value = false): self
    {
        $this->getContainer()->set('is_created_link', $value);
        return $this;
    }

    /**
     * Получение пути размещения значения свойства
     *
     * @return string
     */
    public function getFrom(): string
    {
        return $this->getContainer()->get('out', '');
    }

    /**
     * Установка пути размещения значения свойства
     *
     * @param string $path
     * @return static
     */
    public function setFrom(string $path): self
    {
        $this->getContainer()->set('out', $path);
        return $this;
    }

    /**
     * Код свойства в которое будет записано значение
     *
     * @return string
     */
    public function getTo(): string
    {
        return $this->getContainer()->get('in', '');
    }

    /**
     * Установка кода в который необходимо записать значение
     *
     * @param string $code
     * @return static
     */
    public function setTo(string $code): self
    {
        $this->getContainer()->set('in', $code);
        return $this;
    }

    /**
     * Получение цели значения свойства
     *
     * @return callable|null
     */
    public function getPreparation(): ?callable
    {
        return $this->getContainer()->get('preparation');
    }

    /**
     * Установка цели значения свойства
     *
     * @param callable $target
     * @return static
     */
    public function setPreparation(callable $target): self
    {
        $this->getContainer()->set('preparation', $target);
        return $this;
    }

    /**
     * Получение дочернего элемента
     *
     * @return FieldInterface|null
     */
    public function getChildren(): ?FieldInterface
    {
        return $this->getContainer()->get('children', null);
    }

    /**
     * Получение нормализаторов значения свйоства
     *
     * @return callable|null
     */
    public function getNormalizer(): ?callable
    {
        return $this->getContainer()->get('normalizer');
    }

    /**
     * Указание нормализаторов свойства
     *
     * @param callable $normalizer
     * @return $this
     */
    public function setNormalizer(callable $normalizer): self
    {
        $this->getContainer()->set('normalizer', $normalizer);

        return $this;
    }

    /**
     * Установка дочернего элемента
     *
     * Описание свойства, которое имеет итерационные значения на своем пути
     * Подходит, если необходимо получить ID изображения
     * <item>
     *     <name>NAME</name>
     *     <images>
     *          <image id="35" />
     *          <image id="35" />
     *      </images>
     * </item>
     *
     * @param FieldInterface $children
     * @return $this
     */
    public function setChildren(FieldInterface $children): self
    {
        $this->getContainer()->set('children', $children);
        return $this;
    }

    /**
     * Получение хранилище данных
     *
     * @final
     * @return RepositoryInterface
     */
    final protected function getContainer(): RepositoryInterface
    {
        return $this->container ??= new Memory();
    }
}