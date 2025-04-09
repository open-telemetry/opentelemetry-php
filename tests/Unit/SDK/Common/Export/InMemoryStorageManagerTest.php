<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Export;

use ArrayObject;
use OpenTelemetry\SDK\Common\Export\InMemoryStorageManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Test class for InMemoryStorageManager.
 *
 * This class focuses on testing the `getStorageForMetrics` method.
 */
#[CoversClass(\OpenTelemetry\SDK\Common\Export\InMemoryStorageManager::class)]
class InMemoryStorageManagerTest extends TestCase
{
    public static function getStorageName(): array
    {
        return [
            ['getStorageForMetrics'],
            ['getStorageForLogs'],
            ['getStorageForSpans'],
        ];
    }

    public function test_get_storage_for_metrics_returns_same_instance(): void
    {
        $storageFirstCall = InMemoryStorageManager::getStorageForMetrics();
        $storageSecondCall = InMemoryStorageManager::getStorageForMetrics();
        $this->assertSame($storageFirstCall, $storageSecondCall);
    }

    #[DataProvider('getStorageName')]
    public function test_get_storage_for_metrics_operates_properly($method): void
    {
        /** @var ArrayObject $storage */
        $storage = call_user_func(InMemoryStorageManager::class . '::' . $method);
        $storage->append('test_metric');

        $this->assertCount(1, $storage);
        $this->assertEquals('test_metric', $storage[0]);

        // test the similar when getting storage again
        /** @var ArrayObject $storage */
        $storage = call_user_func(InMemoryStorageManager::class . '::' . $method);
        $this->assertCount(1, $storage);
        $this->assertEquals('test_metric', $storage[0]);
    }
}
