<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics\MetricObserver;

use OpenTelemetry\SDK\Metrics\MetricObserver\CallbackDestructor;
use OpenTelemetry\SDK\Metrics\MetricObserverInterface;
use OpenTelemetry\SDK\Metrics\ReferenceCounterInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Metrics\MetricObserver\CallbackDestructor
 */
final class CallbackDestructorTest extends TestCase
{
    public function test_callback_destructor_cancels_tokens_on_destruct(): void
    {
        $observer = $this->createMock(MetricObserverInterface::class);
        $observer->expects($this->exactly(2))->method('has')->withConsecutive([1], [2])->willReturn(true);
        $observer->expects($this->exactly(2))->method('cancel')->withConsecutive([1], [2]);

        $referenceCounter = $this->createMock(ReferenceCounterInterface::class);
        $referenceCounter->expects($this->exactly(2))->method('release');

        /** @noinspection PhpObjectFieldsAreOnlyWrittenInspection */
        $destructor = new CallbackDestructor($observer, $referenceCounter);
        $destructor->tokens[] = 1;
        $destructor->tokens[] = 2;
        $destructor = null;
    }

    public function test_callback_destructor_skips_not_active_token(): void
    {
        $observer = $this->createMock(MetricObserverInterface::class);
        $observer->expects($this->exactly(2))->method('has')->willReturn(false);

        $referenceCounter = $this->createMock(ReferenceCounterInterface::class);
        $referenceCounter->expects($this->never())->method('release');

        /** @noinspection PhpObjectFieldsAreOnlyWrittenInspection */
        $destructor = new CallbackDestructor($observer, $referenceCounter);
        $destructor->tokens[] = 1;
        $destructor->tokens[] = 2;
        $destructor = null;
    }
}
