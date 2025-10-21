<?php

namespace Sholokhov\Exchange;

use Exception;
use Throwable;
use ReflectionException;

use Sholokhov\Exchange\Builder\ExchangeResultBuilder;
use Sholokhov\Exchange\Dispatcher\EventDispatchableTrait;
use Sholokhov\Exchange\Factory\Exchange\ValidatorFactory;
use Sholokhov\Exchange\Repository\RepositoryInterface;
use Sholokhov\Exchange\Repository\Types\Memory;
use Sholokhov\Exchange\Target\Attributes\Event;
use Sholokhov\Exchange\Events\ExchangeEvent;
use Sholokhov\Exchange\Messages\ExchangeResultInterface;
use Sholokhov\Exchange\Messages\Type\Error;

use Bitrix\Main\NotImplementedException;

use Psr\Log\LoggerAwareTrait;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Базовый класс обмена данными
 *
 * @package Exchange
 */
abstract class AbstractExchange implements ExchangeInterface, EventDispatcherInterface
{
    use LoggerAwareTrait,
        EventDispatchableTrait;

    /**
     * @var callable|null
     */
    private $resultFactory = null;

    /**
     * timestamp старта обмена
     *
     * @var int
     */
    private int $dateUp = 0;

    /**
     * Валидаторы обмена
     *
     * @var array
     */
    private readonly array $validators;

    /**
     * Временное хранилище обмена
     *
     * @var RepositoryInterface
     */
    private readonly RepositoryInterface $cache;

    /**
     * Логика обмена данных
     *
     * @param iterable $source
     * @param ExchangeResultInterface $result
     * @return void
     */
    abstract protected function logic(iterable $source, ExchangeResultInterface $result): void;

    /**
     * @final
     * @param iterable $source
     * @return ExchangeResultInterface
     * @throws NotImplementedException
     * @throws ReflectionException
     * @throws Exception
     */
    final public function execute(iterable $source): ExchangeResultInterface
    {
        $result = $this->createResult();

        $this->validate($result);
        if (!$result->isSuccess()) {
            return $result;
        }

        $this->beforeRunEvent();

        try {
            $this->logic($source, $result);
        } catch (Throwable $e) {
            $this->handleException($e, $result);
        }

        $this->afterRunEvent();

        return $result;
    }

    /**
     * Указание генератора хранилища
     *
     * @param callable $factory
     * @return $this
     */
    final public function setResultRepositoryFactory(callable $factory): static
    {
        $this->resultFactory = $factory;
        return $this;
    }

    /**
     * Получение генератора хранилища
     *
     * @return callable|null
     */
    protected function getResultRepositoryFactory(): ?callable
    {
        return $this->resultFactory;
    }

    /**
     * Получение запуска обмена
     *
     * @return int
     */
    protected function getDateStarted(): int
    {
        return $this->dateUp;
    }

    /**
     * Создание объекта хранения результатов обмена
     *
     * @return ExchangeResultInterface
     * @throws Exception
     */
    protected function createResult(): ExchangeResultInterface
    {
        $factory = $this->getResultRepositoryFactory();
        return ExchangeResultBuilder::create($this, $factory);
    }

    /**
     * Обработка ошибки обмена
     *
     * @param Throwable $exception
     * @param ExchangeResultInterface $result
     * @return void
     */
    protected function handleException(Throwable $exception, ExchangeResultInterface $result): void
    {
        $this->logger?->error($exception->getMessage(), ['exception' => $exception]);
        $result->addError(new Error($exception->getMessage()));
    }

    /**
     * Валидация обмена
     *
     * @param ExchangeResultInterface $result
     * @return void
     */
    protected function validate(ExchangeResultInterface $result): void
    {
        foreach ($this->getValidators() as $validator) {
            $validateResult = $validator->validate($this);

            if (!$validateResult->isSuccess()) {
                $result->addErrors($validateResult->getErrors());
            }
        }
    }

    /**
     * Кэш обмена
     *
     * @return RepositoryInterface
     */
    protected function getCache(): RepositoryInterface
    {
        return $this->cache ??= new Memory;
    }

    /**
     * Доступные валидаторы обмена
     *
     * @final
     * @return iterable
     */
    final protected function getValidators(): iterable
    {
        return $this->validators ??= ValidatorFactory::create($this);
    }

    /**
     * Событие перед запуском обмена
     *
     * @return void
     * @throws NotImplementedException
     * @throws ReflectionException
     */
    private function beforeRunEvent(): void
    {
        $this->dateUp = time();
        $this->dispatch(new Events\Event(ExchangeEvent::BeforeRun->value));
    }

    /**
     * Событие после окончания обмена
     *
     * @return void
     * @throws NotImplementedException
     * @throws ReflectionException
     */
    private function afterRunEvent(): void
    {
        $this->dispatch(new Events\Event(ExchangeEvent::AfterRun->value));
        $this->dateUp = 0;
    }
}
