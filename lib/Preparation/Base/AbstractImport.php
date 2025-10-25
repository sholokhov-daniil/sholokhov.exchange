<?php

namespace Sholokhov\Exchange\Preparation\Base;

use Exception;
use InvalidArgumentException;

use Sholokhov\Exchange\Preparation\AbstractPrepare;

use Sholokhov\Exchange\ExchangeInterface;
use Sholokhov\Exchange\Fields\FieldInterface;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerAwareInterface;

/**
 * Преобразует значение имеющего связь к сущности
 *
 * @package Preparation
 */
abstract class AbstractImport extends AbstractPrepare implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * Список поддерживаемых ключей связи.
     *
     * Если массив пустой, то будут поддерживаться все виды значений
     *
     * @var array|string[]
     */
    protected array $supportedPrimaries = [];

    /**
     * Ключ по которому будет производиться проверка уникальности
     *
     * @var string
     */
    protected readonly string $primary;

    /**
     * Связующий ключ по умолчанию
     *
     * @var string
     */
    protected string $defaultPrimary = "XML_ID";

    /**
     * Получение импорта значения
     *
     * @param FieldInterface $field
     * @return ExchangeInterface
     */
    abstract protected function getTarget(FieldInterface $field): ExchangeInterface;

    /**
     * Конфигурация импорта значения
     *
     * @param ExchangeInterface $target
     * @return void
     */
    abstract protected function configurationTarget(ExchangeInterface $target): void;

    /**
     * @param string|null $primary Ключ по которому будет производиться проверка уникальности
     */
    public function __construct(?string $primary = null)
    {
        $this->primary = $primary ?: $this->defaultPrimary;
        $this->checkPrimary();
    }

    /**
     * Логика преобразования значения
     *
     * @final
     * @param mixed $value Значение, которое необходимо преобразовать
     * @param FieldInterface $field Свойство, которое преобразуется
     * @return mixed
     * @throws Exception
     */
    final protected function logic(mixed $value, FieldInterface $field): mixed
    {
        if ($value == '') {
            return 0;
        }

        $target = $this->getTarget($field);
        $this->configurationTarget($target);

        $result = $target->execute([[$value]]);
        if (!$result->isSuccess()) {
            throw new Exception(implode(PHP_EOL, $result->getErrorMessages()));
        }

        $data = $result->getData()?->get();

        return $this->normalize($data, $field);
    }

    /**
     * Преобразование результата работы цели в конечный результат.
     *
     * Метод предназначен для переопределения
     *
     * @param mixed $value Результат цели, который необходимо нормализовать
     * @param FieldInterface $field
     * @return mixed
     *
     * @todo Потом перейти на цепочку атрибутов и выполнять только первый от последнего ребенка
     */
    protected function normalize(mixed $value, FieldInterface $field): mixed
    {
        return $value;
    }

    /**
     * Проверка валидности первичного ключа
     *
     * @final
     * @return void
     */
    final protected function checkPrimary(): void
    {
        if (!empty($this->supportedPrimaries) && !in_array($this->primary, $this->supportedPrimaries)) {
            throw new InvalidArgumentException(sprintf('Primary key "%s" are not allowed', $this->primary));
        }
    }
}