<?php

namespace Sholokhov\Exchange\Fields;

/**
 * Описание настроек свойства
 *
 * @package Field
 */
interface FieldInterface
{
    /**
     * Поле отвечает за идентификацию значений.
     * На основе данного поля происходит определение наличия импортированного значения
     * или обновление существующего.
     *
     * @return bool
     */
    public function isPrimary(): bool;

    /**
     * Свойство отвечает за хранение хэша
     *
     * @return bool
     */
    public function isHash(): bool;

    /**
     * При отсутствии связующей сущности произвести его создание.
     *
     * @example Описываем свойство типа: привязка к элементу инфоблока. Если значение true, то при отсутствии элемента оно будет создано
     *
     * @return bool
     */
    public function isCreatedLink(): bool;

    /**
     * Получение пути хранения значения
     *
     * @return string
     */
    public function getFrom(): string;

    /**
     * Код свойства в которое необходимо записать значение
     *
     * @return string
     */
    public function getTo(): string;

    /**
     * Цель значения
     *
     * @return ?callable
     */
    public function getPreparation(): ?callable;

    /**
     * Получение дочернего элемента
     *
     * @return FieldInterface|null
     */
    public function getChildren(): ?FieldInterface;

    /**
     * Получение подготовителя значения перед преобразованием на основе настроек сущности
     *
     * @return callable|null
     */
    public function getNormalizer(): ?callable;
}