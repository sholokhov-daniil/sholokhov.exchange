<?php

namespace Sholokhov\Exchange\Provider\Entity;

interface IBlockElementProviderInterface extends EntityProviderInterface
{
    /**
     * Устанавливает свойства элементов для выборки
     *
     * @param array $properties Массив кодов свойств
     * @return $this
     */
    public function setProperties(array $properties = []): static;

    /**
     * Добавление свойства элемента
     *
     * @param string $code
     * @return $this
     */
    public function addProperty(string $code): static;
}