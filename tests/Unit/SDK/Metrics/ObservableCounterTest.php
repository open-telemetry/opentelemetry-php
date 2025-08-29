<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics;

use ArrayAccess;
use OpenTelemetry\API\Metrics\ObservableCallbackInterface;
use OpenTelemetry\SDK\Metrics\Instrument;
use OpenTelemetry\SDK\Metrics\MetricRegistry\MetricWriterInterface;
use OpenTelemetry\SDK\Metrics\ObservableCounter;
use OpenTelemetry\SDK\Metrics\ReferenceCounterInterface;
use PHPUnit\Framework\TestCase;

class ObservableCounterTest extends TestCase
{
    private $mockWriter;
    private $mockInstrument;
    private $mockReferenceCounter;
    private $mockDestructors;
    private $observableCounter;

    protected function setUp(): void
    {
        $this->mockWriter = $this->createMock(MetricWriterInterface::class);
        $this->mockInstrument = $this->createMock(Instrument::class);
        $this->mockReferenceCounter = $this->createMock(ReferenceCounterInterface::class);
        $this->mockDestructors = $this->createMock(ArrayAccess::class);

        $this->observableCounter = new ObservableCounter(
            $this->mockWriter,
            $this->mockInstrument,
            $this->mockReferenceCounter,
            $this->mockDestructors
        );
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\ObservableCounter::__construct
     */
    public function test_constructor_calls_reference_counter_acquire(): void
    {
        $this->mockReferenceCounter->expects($this->once())
            ->method('acquire');

        new ObservableCounter(
            $this->mockWriter,
            $this->mockInstrument,
            $this->mockReferenceCounter,
            $this->mockDestructors
        );
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\ObservableCounter::__destruct
     */
    public function test_destructor_calls_reference_counter_release(): void
    {
        $this->mockReferenceCounter->expects($this->once())
            ->method('release');

        $counter = new ObservableCounter(
            $this->mockWriter,
            $this->mockInstrument,
            $this->mockReferenceCounter,
            $this->mockDestructors
        );

        // Trigger destructor
        unset($counter);
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\ObservableCounter::getHandle
     */
    public function test_get_handle_returns_instrument(): void
    {
        $handle = $this->observableCounter->getHandle();

        $this->assertSame($this->mockInstrument, $handle);
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\ObservableCounter::isEnabled
     */
    public function test_is_enabled_delegates_to_writer(): void
    {
        $this->mockWriter->expects($this->once())
            ->method('enabled')
            ->with($this->mockInstrument)
            ->willReturn(true);

        $result = $this->observableCounter->isEnabled();

        $this->assertTrue($result);
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\ObservableCounter::isEnabled
     */
    public function test_is_enabled_returns_false_when_writer_returns_false(): void
    {
        $this->mockWriter->expects($this->once())
            ->method('enabled')
            ->with($this->mockInstrument)
            ->willReturn(false);

        $result = $this->observableCounter->isEnabled();

        $this->assertFalse($result);
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\ObservableCounter::observe
     */
    public function test_observe_returns_observable_callback_interface(): void
    {
        $callback = function ($observer) {
            // Test callback
        };

        $result = $this->observableCounter->observe($callback);

        $this->assertInstanceOf(ObservableCallbackInterface::class, $result);
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\ObservableCounter::observe
     */
    public function test_observe_with_closure_callback(): void
    {
        $callback = function ($observer) {
            $observer->observe(42, ['label' => 'value']);
        };

        $result = $this->observableCounter->observe($callback);

        $this->assertInstanceOf(ObservableCallbackInterface::class, $result);
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\ObservableCounter::observe
     */
    public function test_observe_with_static_callback(): void
    {
        $callback = [self::class, 'staticCallback'];

        $result = $this->observableCounter->observe($callback);

        $this->assertInstanceOf(ObservableCallbackInterface::class, $result);
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\ObservableCounter::observe
     */
    public function test_observe_with_array_callback(): void
    {
        $callback = [$this, 'instanceCallback'];

        $result = $this->observableCounter->observe($callback);

        $this->assertInstanceOf(ObservableCallbackInterface::class, $result);
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\ObservableCounter
     */
    public function test_implements_instrument_handle(): void
    {
        $this->assertInstanceOf(\OpenTelemetry\SDK\Metrics\InstrumentHandle::class, $this->observableCounter);
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\ObservableCounter
     */
    public function test_implements_observable_counter_interface(): void
    {
        $this->assertInstanceOf(\OpenTelemetry\API\Metrics\ObservableCounterInterface::class, $this->observableCounter);
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\ObservableCounter
     */
    public function test_class_is_not_final(): void
    {
        $reflection = new \ReflectionClass(ObservableCounter::class);
        $this->assertFalse($reflection->isFinal());
    }

    // Helper methods for testing callbacks
    public static function staticCallback($observer): void
    {
        // Static callback for testing
    }

    public function instanceCallback($observer): void
    {
        // Instance callback for testing
    }
}
