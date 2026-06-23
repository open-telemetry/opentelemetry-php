<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics;

use ArrayAccess;
use OpenTelemetry\SDK\Metrics\MetricRegistry\MetricWriterInterface;
use OpenTelemetry\SDK\Metrics\ObservableCallbackDestructor;
use OpenTelemetry\SDK\Metrics\ReferenceCounterInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use WeakMap;

#[CoversClass(ObservableCallbackDestructor::class)]
final class ObservableCallbackDestructorTest extends TestCase
{
    public function test_destruct_unregisters_callbacks_and_releases_counters(): void
    {
        $writer = $this->createMock(MetricWriterInterface::class);
        $writer->expects($this->exactly(2))
            ->method('unregisterCallback')
            ->willReturnCallback(function (int $id): void {
                $this->assertContains($id, [1, 2]);
            });

        $counter1 = $this->createMock(ReferenceCounterInterface::class);
        $counter1->expects($this->once())->method('release');

        $counter2 = $this->createMock(ReferenceCounterInterface::class);
        $counter2->expects($this->once())->method('release');

        /** @var ArrayAccess<object, ObservableCallbackDestructor> $destructors */
        $destructors = new WeakMap();

        $destructor = new ObservableCallbackDestructor($destructors, $writer);
        $destructor->callbackIds[1] = $counter1;
        $destructor->callbackIds[2] = $counter2;

        // Trigger __destruct
        unset($destructor);
    }

    public function test_destruct_with_no_callbacks(): void
    {
        $writer = $this->createMock(MetricWriterInterface::class);
        $writer->expects($this->never())->method('unregisterCallback');

        /** @var ArrayAccess<object, ObservableCallbackDestructor> $destructors */
        $destructors = new WeakMap();

        $destructor = new ObservableCallbackDestructor($destructors, $writer);

        // Should not throw
        unset($destructor);
    }

    public function test_callback_ids_is_initially_empty(): void
    {
        $writer = $this->createMock(MetricWriterInterface::class);
        /** @var ArrayAccess<object, ObservableCallbackDestructor> $destructors */
        $destructors = new WeakMap();

        $destructor = new ObservableCallbackDestructor($destructors, $writer);

        $this->assertEmpty($destructor->callbackIds);
    }
}
