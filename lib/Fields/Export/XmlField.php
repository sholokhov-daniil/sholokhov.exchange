<?php

namespace Sholokhov\Exchange\Fields\Export;

use Sholokhov\Exchange\Fields\Field;
use Sholokhov\Exchange\Fields\FieldInterface;

/**
 * Описание полей экспорта в xml
 */
class XmlField extends Field implements XmlFieldInterface
{
    /**
     * Получение доступных атрибутов
     *
     * @return FieldInterface[]
     */
    public function getAttributes(): array
    {
        return $this->getContainer()->get('attributes', []);
    }

    /**
     * Добавление атрибута у тега
     *
     * @param FieldInterface $attribute
     * @return $this
     */
    public function addAttribute(FieldInterface $attribute): static
    {
        $attributes = $this->getAttributes();
        $attributes[] = $attribute;

        return $this->setAttributes($attributes);
    }

    /**
     * Установка списка атрибутов у тега
     *
     * @param array $attributes
     * @return $this
     */
    public function setAttributes(array $attributes): static
    {
        $this->getContainer()->set('attributes', $attributes);
        return $this;
    }

    /**
     * Значение является множественным
     *
     * @return string
     */
    public function getChildrenTag(): string
    {
        return (string)$this->getContainer()->get('children_tag', '');
    }

    /**
     * Указание вложенного тега
     *
     * При указании дочернего тега значение автоматически считается множественным
     *
     * @param string $name
     * @return $this
     */
    public function setChildrenTag(string $name): static
    {
        $this->getContainer()->set('children_tag', $name);
        return $this;
    }
}