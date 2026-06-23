<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics\StalenessHandler;

use OpenTelemetry\SDK\Metrics\ReferenceCounterInterface;
use OpenTelemetry\SDK\Metrics\StalenessHandler\MultiReferenceCounter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MultiReferenceCounter::class)]
final class MultiReferenceCounterTest extends TestCase
{
    public function test_acquire_delegates_to_all_counters(): void
    {
        $counter1 = $this->createMock(ReferenceCounterInterface::class);
        $counter1->expects($this->once())->method('acquire')->with(false);

        $counter2 = $this->createMock(ReferenceCounterInterface::class);
        $counter2->expects($this->once())->method('acquire')->with(false);

        $multi = new MultiReferenceCounter([$counter1, $counter2]);
        $multi->acquire();
    }

    public function test_acquire_persistent_delegates_to_all_counters(): void
    {
        $counter1 = $this->createMock(ReferenceCounterInterface::class);
        $counter1->expects($this->once())->method('acquire')->with(true);

        $counter2 = $this->createMock(ReferenceCounterInterface::class);
        $counter2->expects($this->once())->method('acquire')->with(true);

        $multi = new MultiReferenceCounter([$counter1, $counter2]);
        $multi->acquire(true);
    }

    public function test_release_delegates_to_all_counters(): void
    {
        $counter1 = $this->createMock(ReferenceCounterInterface::class);
        $counter1->expects($this->once())->method('release');

        $counter2 = $this->createMock(ReferenceCounterInterface::class);
        $counter2->expects($this->once())->method('release');

        $multi = new MultiReferenceCounter([$counter1, $counter2]);
        $multi->release();
    }

    public function test_empty_counters_does_not_throw(): void
    {
        $multi = new MultiReferenceCounter([]);
        $multi->acquire();
        $multi->release();

        $this->assertTrue(true);
    }
}
