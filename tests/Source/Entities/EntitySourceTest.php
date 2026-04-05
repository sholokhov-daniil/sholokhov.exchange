<?php

namespace Sholokhov\Exchange\Source\Entities;

use Sholokhov\Exchange\Exception\Source\SourceException;
use Sholokhov\Exchange\Provider\Entity\EntityProviderInterface;

use Bitrix\Main\DB\Result;
use Bitrix\Main\DB\ArrayResult;

use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

/**
 * Тесты для {@see EntitySource}
 *
 * Проверяет:
 * - батчевую итерацию
 * - курсорную пагинацию (>ID)
 * - корректность rewind()
 * - обработку ошибок
 */
class EntitySourceTest extends TestCase
{
    /**
     * Создаёт фейковый Result на основе массива данных
     *
     * @param array<int, array<string, mixed>> $rows
     *
     * @return Result
     */
    private function makeResult(array $rows): Result
    {
        return new ArrayResult($rows);
    }

    /**
     * Создаёт mock-провайдер с поддержкой курсорной пагинации (>ID)
     *
     * Эмулирует:
     * - батчевую загрузку (по 2 элемента)
     * - фильтрацию через >ID
     *
     * @return EntityProviderInterface
     */
    private function makeProvider(): EntityProviderInterface
    {
        $data = [
            ['ID' => 1],
            ['ID' => 2],
            ['ID' => 3],
            ['ID' => 4],
        ];

        $provider = $this->createMock(EntityProviderInterface::class);

        $filter = [];

        $provider->method('setOrder')->willReturnSelf();

        $provider->method('setFilter')
            ->willReturnCallback(function ($f) use (&$filter, $provider) {
                $filter = $f;
                return $provider;
            });

        $provider->method('getFilter')
            ->willReturnCallback(function () use (&$filter) {
                return $filter;
            });

        $provider->method('query')
            ->willReturnCallback(function () use (&$data, &$filter) {
                $lastId = $filter['>ID'] ?? 0;

                $batch = array_values(array_filter(
                    $data,
                    fn($item) => $item['ID'] > $lastId
                ));

                $batch = array_slice($batch, 0, 2);

                return empty($batch) ? null : $this->makeResult($batch);
            });

        return $provider;
    }

    /**
     * Проверяет полную итерацию по всем батчам
     *
     * Ожидается, что данные будут получены последовательно:
     * 1 → 2 → 3 → 4
     */
    public function testIterationOverBatches(): void
    {
        $source = new EntitySource($this->makeProvider());

        $result = [];

        foreach ($source as $item) {
            $result[] = $item['ID'];
        }

        $this->assertEquals([1, 2, 3, 4], $result);
    }

    /**
     * Проверяет корректную работу rewind()
     *
     * Итератор должен полностью сбрасывать состояние
     * и повторно возвращать те же данные
     */
    public function testRewind(): void
    {
        $source = new EntitySource($this->makeProvider());

        $firstRun = [];
        foreach ($source as $item) {
            $firstRun[] = $item['ID'];
        }

        $secondRun = [];
        foreach ($source as $item) {
            $secondRun[] = $item['ID'];
        }

        $this->assertEquals($firstRun, $secondRun);
    }

    /**
     * Проверяет завершение итерации при отсутствии данных
     *
     * @return void
     * @throws Exception
     */
    public function testStopsWhenNoMoreData(): void
    {
        $provider = $this->createMock(EntityProviderInterface::class);

        $provider->method('setOrder')->willReturnSelf();
        $provider->method('setFilter')->willReturnSelf();

        $provider->method('query')->willReturn(null);

        $source = new EntitySource($provider);

        $items = [];

        foreach ($source as $item) {
            $items[] = $item;
        }

        $this->assertEmpty($items);
    }

    /**
     * Проверяет выброс исключения при отсутствии ID
     *
     * @throws Exception
     */
    public function testThrowsExceptionWhenNoId(): void
    {
        $this->expectException(SourceException::class);

        $provider = $this->createMock(EntityProviderInterface::class);

        $provider->method('setOrder')->willReturnSelf();
        $provider->method('setFilter')->willReturnSelf();

        $provider->method('query')->willReturn(
            $this->makeResult([
                ['NAME' => 'BROKEN']
            ])
        );

        $source = new EntitySource($provider);

        foreach ($source as $ignored) {
            // должно упасть
        }
    }

    /**
     * Проверяет, что данные загружаются батчами (несколько вызовов query)
     *
     * @return void
     * @throws Exception
     */
    public function testQueryCalledMultipleTimes(): void
    {
        $provider = $this->createMock(EntityProviderInterface::class);

        $provider->method('setOrder')->willReturnSelf();
        $provider->method('setFilter')->willReturnSelf();

        $provider->expects($this->exactly(3))
            ->method('query')
            ->willReturnCallback(function () {
                static $call = 0;
                $call++;

                return match ($call) {
                    1 => $this->makeResult([['ID' => 1]]),
                    2 => $this->makeResult([['ID' => 2]]),
                    default => null,
                };
            });

        $source = new EntitySource($provider);

        foreach ($source as $ignored) {
            // просто прогон
        }
    }
}
