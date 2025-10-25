<?php

namespace Sholokhov\Exchange\Events;

class Event implements EventInterface
{
    private readonly string $type;
    private readonly array $parameters;

    /**
     * @param string $type Тип события
     */
    public function __construct(string $type, array $parameters = [])
    {
        $this->type = $type;
        $this->parameters = $parameters;
    }

    /**
     * Тип события
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Параметры события
     *
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Получение параметра по ключу
     *
     * @param string|int $key
     * @return mixed
     */
    public function getParameter(string|int $key): mixed
    {
        return $this->parameters[$key] ?? null;
    }
}