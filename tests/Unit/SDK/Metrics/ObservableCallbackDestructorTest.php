<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics;

use ArrayAccess;
use OpenTelemetry\SDK\Metrics\MetricRegistry\MetricWriterInterface;
use OpenTelemetry\SDK\Metrics\ObservableCallbackDestructor;
use OpenTelemetry\SDK\Metrics\ReferenceCounterInterface;
use PHPUnit\Framework\TestCase;

class ObservableCallbackDestructorTest extends TestCase
{
    private $mockWriter;
    private $mockDestructors;
    private $mockReferenceCounter1;
    private $mockReferenceCounter2;

    #[\Override]
    protected function setUp(): void
    {
        $this->mockWriter = $this->createMock(MetricWriterInterface::class);
        $this->mockDestructors = $this->createMock(ArrayAccess::class);
        $this->mockReferenceCounter1 = $this->createMock(ReferenceCounterInterface::class);
        $this->mockReferenceCounter2 = $this->createMock(ReferenceCounterInterface::class);
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\ObservableCallbackDestructor::__destruct
     */
    public function test_destructor_with_multiple_callbacks(): void
    {
        $callbackId1 = 123;
        $callbackId2 = 456;

        $destructor = new ObservableCallbackDestructor(
            $this->mockDestructors,
            $this->mockWriter
        );

        // Set up callbackIds
        $destructor->callbackIds = [
            $callbackId1 => $this->mockReferenceCounter1,
            $callbackId2 => $this->mockReferenceCounter2,
        ];

        $this->mockWriter->expects($this->exactly(2))
            ->method('unregisterCallback')
            ->willReturnCallback(function ($id) {
                return $id;
            });

        $this->mockReferenceCounter1->expects($this->once())
            ->method('release');

        $this->mockReferenceCounter2->expects($this->once())
            ->method('release');

        // Trigger destructor
        unset($destructor);
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\ObservableCallbackDestructor::__destruct
     */
    public function test_destructor_with_single_callback(): void
    {
        $callbackId = 123;

        $destructor = new ObservableCallbackDestructor(
            $this->mockDestructors,
            $this->mockWriter
        );

        // Set up callbackIds
        $destructor->callbackIds = [
            $callbackId => $this->mockReferenceCounter1,
        ];

        $this->mockWriter->expects($this->once())
            ->method('unregisterCallback')
            ->with($callbackId);

        $this->mockReferenceCounter1->expects($this->once())
            ->method('release');

        // Trigger destructor
        unset($destructor);
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\ObservableCallbackDestructor::__destruct
     */
    public function test_destructor_with_no_callbacks(): void
    {
        $destructor = new ObservableCallbackDestructor(
            $this->mockDestructors,
            $this->mockWriter
        );

        // Set up empty callbackIds
        $destructor->callbackIds = [];

        $this->mockWriter->expects($this->never())
            ->method('unregisterCallback');

        $this->mockReferenceCounter1->expects($this->never())
            ->method('release');

        // Trigger destructor
        unset($destructor);
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\ObservableCallbackDestructor::__construct
     */
    public function test_constructor_sets_properties(): void
    {
        $destructor = new ObservableCallbackDestructor(
            $this->mockDestructors,
            $this->mockWriter
        );

        $this->assertSame($this->mockDestructors, $destructor->destructors);
        $this->assertEquals([], $destructor->callbackIds);
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\ObservableCallbackDestructor
     */
    public function test_callback_ids_property_is_public(): void
    {
        $destructor = new ObservableCallbackDestructor(
            $this->mockDestructors,
            $this->mockWriter
        );

        $callbackIds = [123 => $this->mockReferenceCounter1];
        $destructor->callbackIds = $callbackIds;

        $this->assertEquals($callbackIds, $destructor->callbackIds);
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\ObservableCallbackDestructor
     */
    public function test_destructors_property_is_public(): void
    {
        $destructor = new ObservableCallbackDestructor(
            $this->mockDestructors,
            $this->mockWriter
        );

        $this->assertSame($this->mockDestructors, $destructor->destructors);
    }
}
