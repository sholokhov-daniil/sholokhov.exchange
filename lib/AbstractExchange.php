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
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
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

    protected readonly Memory $options;
    protected array $validators;
    protected readonly RepositoryInterface $cache;
    private int $dateUp = 0;

    /**
     * Логика обмена данных
     *
     * @param iterable $source
     * @param ExchangeResultInterface $result
     * @return void
     */
    abstract protected function logic(iterable $source, ExchangeResultInterface $result): void;

    /**
     * Деактивация элемента сущности по итогам обмена.
     * Предназначен, для переопределения наследниками
     *
     * @return void
     */
    public function deactivate(): void
    {
    }

    /**
     * Конфигурация обмена
     * Предназначен, для переопределения наследниками
     *
     * @return void
     */
    protected function configuration(): void
    {
    }

    /**
     * @todo: Сделать возможность передачи объекта с настройками  - так будет проще настраивать обмен
     * @param array $options Конфигурация обмена
     * @throws Exception
     */
    public function __construct(array $options = [])
    {
        $this->validators = ValidatorFactory::create($this);

        // TODO: Необходимо создать нормализовать параметры
        $this->options = new Memory($options);
        $this->cache = new Memory;

        $this->configuration();
    }

    /**
     * @final
     * @param iterable $source
     * @return ExchangeResultInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws NotImplementedException
     * @throws ReflectionException
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
     * Получение хэша обмена
     *
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getHash(): string
    {
        return (string)$this->getOptions()->get('hash', '');
    }

    /**
     * Получение конфигурации обмена
     *
     * @return ContainerInterface
     */
    public function getOptions(): ContainerInterface
    {
        return $this->options;
    }

    /**
     * Указание генератора хранилища
     *
     * @param callable $factory
     * @return $this
     */
    final public function setResultRepositoryFactory(callable $factory): static
    {
        $this->options->set('result_repository', $factory);
        return $this;
    }

    /**
     * Получение генератора хранилища
     *
     * @return callable|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getResultRepositoryFactory(): ?callable
    {
        return $this->getOptions()->get('result_repository');
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
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
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
        foreach ($this->validators as $validator) {
            $validateResult = $validator->validate($this);

            if (!$validateResult->isSuccess()) {
                $result->addErrors($validateResult->getErrors());
            }
        }
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
