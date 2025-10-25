<?php

namespace Sholokhov\Exchange\Repository\Result;

use Exception;
use Stringable;

use Sholokhov\Exchange\ORM\ResultTable;

use Ramsey\Uuid\Uuid;

/**
 * Хранение результата обмена в отдельном хранилище по ID
 *
 * @internal
 * @notice Еще не тестировался и находится в разработке
 * @package Repository
 */
class UidRepository implements ResultRepositoryInterface
{
    /**
     * Идентификатор хранилища
     *
     * @var string
     */
    private readonly string $uid;

    /**
     * @param string|null $uid Идентификатор хранилища, если не задано, то по умолчанию используется {@see Uuid::uuid4()}
     */
    public function __construct(?string $uid = null)
    {
        $this->uid = $uid ?: Uuid::uuid4()->toString();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->uid;
    }

    /**
     * Получение идентификатора хранилища
     *
     * @return string
     */
    public function get(): string
    {
        return $this->uid;
    }

    /**
     * @param Stringable|string $value
     * @return void
     *
     * @throws Exception
     */
    public function add(Stringable|string $value): void
    {
        $pid = getmypid();

        if (!$pid) {
            throw new Exception('Error getting the PID of the current process');
        }

        ResultTable::add([
            ResultTable::PC_UID => $this->get(),
            ResultTable::PC_PID => $pid,
            ResultTable::PC_VALUE => (string)$value,
        ]);
    }
}