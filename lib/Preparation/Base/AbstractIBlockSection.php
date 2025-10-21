<?php

namespace Sholokhov\Exchange\Preparation\Base;

use Exception;
use ReflectionException;

use Sholokhov\Exchange\Factory\Result\SimpleFactory;
use Sholokhov\Exchange\Fields\Field;
use Sholokhov\Exchange\Fields\FieldInterface;
use Sholokhov\Exchange\Messages\DataResultInterface;
use Sholokhov\Exchange\Messages\ExchangeResultInterface;
use Sholokhov\Exchange\Messages\Type\DataResult;
use Sholokhov\Exchange\Preparation\AbstractPrepare;
use Sholokhov\Exchange\Repository\IBlock\SectionRepository;
use Sholokhov\Exchange\Repository\Map\MappingRegistry;
use Sholokhov\Exchange\Target\Import\IBlock\Section;

use Bitrix\Main\NotImplementedException;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Sholokhov\Exchange\Target\Options\Import\IBlock\IBlockOption;

/**
 * @package Preparation
 */
abstract class AbstractIBlockSection extends AbstractPrepare implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * Идентификатор информационного блока к которому привязано свойство
     *
     * @param FieldInterface $field Свойство на основе которого производится поиск
     * @return int
     */
    abstract protected function getFieldIBlockID(FieldInterface $field): int;

    /**
     * @param string $primary Ключ по которому будет производиться проверка уникальности
     */
    public function __construct(private readonly string $primary = 'XML_ID')
    {
    }

    /**
     * Логика преобразование значения в идентификатор раздела информационного блока
     *
     * @param mixed $value
     * @param FieldInterface $field
     * @return int
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws NotImplementedException
     * @throws ReflectionException
     */
    protected function logic(mixed $value, FieldInterface $field): int
    {
        if (is_null($value) || $value === '') {
            return 0;
        }

        if ($field->isCreatedLink()) {
            $result = $this->runExchange($value, $field)->getData()->get();
            $result = reset($result);
        } else {
            $result = $this->runRepository($value, $field)->getData();
        }

        // todo: Обработка ошибок

        return $result;
    }

    /**
     * Импортирование раздела
     *
     * @param mixed $value Преобразуемое значение
     * @param FieldInterface $field Свойство на основе которого будет производиться импорт раздела
     * @return ExchangeResultInterface
     * @throws NotImplementedException
     * @throws ReflectionException
     * @throws Exception
     */
    private function runExchange(mixed $value, FieldInterface $field): ExchangeResultInterface
    {
        $options = new IBlockOption($this->getFieldIBlockID($field));
        $exchange = new Section($options);
        $exchange->setResultRepositoryFactory(new SimpleFactory);

        $mapping = new MappingRegistry;
        $mapping->setFields([
            (new Field)
                ->setFrom(0)
                ->setTo($this->primary)
                ->setPrimary(),
        ]);

        $exchange->setMappingRegistry($mapping);

        if ($this->logger) {
            $exchange->setLogger($this->logger);
        }

        return $exchange->execute([[$value]]);
    }

    /**
     * Поиск раздела по первичному ключу
     *
     * @param mixed $value Преобразуемое значение
     * @param FieldInterface $field Свойство на основе которого будет производиться поиск раздела
     * @return DataResultInterface
     */
    private function runRepository(mixed $value, FieldInterface $field): DataResultInterface
    {
        $result = new DataResult;

        $repository = new SectionRepository([
            'iblock_id' => $this->getFieldIBlockID($field),
            'primary' => $this->primary
        ]);

        return $result->setData((int)$repository->get($value)['ID'] ?? 0);
    }

    /**
     * Нормализация результата импорта значения
     *
     * @param mixed $value
     * @return mixed
     */
    protected function normalize(mixed $value): int
    {
        return is_array($value) ? (int)reset($value) : 0;
    }
}