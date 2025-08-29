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
    public function testConstructorCallsReferenceCounterAcquire(): void
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
    public function testDestructorCallsReferenceCounterRelease(): void
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
    public function testGetHandleReturnsInstrument(): void
    {
        $handle = $this->observableCounter->getHandle();
        
        $this->assertSame($this->mockInstrument, $handle);
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\ObservableCounter::isEnabled
     */
    public function testIsEnabledDelegatesToWriter(): void
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
    public function testIsEnabledReturnsFalseWhenWriterReturnsFalse(): void
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
    public function testObserveReturnsObservableCallbackInterface(): void
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
    public function testObserveWithClosureCallback(): void
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
    public function testObserveWithStaticCallback(): void
    {
        $callback = [self::class, 'staticCallback'];
        
        $result = $this->observableCounter->observe($callback);
        
        $this->assertInstanceOf(ObservableCallbackInterface::class, $result);
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\ObservableCounter::observe
     */
    public function testObserveWithArrayCallback(): void
    {
        $callback = [$this, 'instanceCallback'];
        
        $result = $this->observableCounter->observe($callback);
        
        $this->assertInstanceOf(ObservableCallbackInterface::class, $result);
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\ObservableCounter
     */
    public function testImplementsInstrumentHandle(): void
    {
        $this->assertInstanceOf(\OpenTelemetry\SDK\Metrics\InstrumentHandle::class, $this->observableCounter);
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\ObservableCounter
     */
    public function testImplementsObservableCounterInterface(): void
    {
        $this->assertInstanceOf(\OpenTelemetry\API\Metrics\ObservableCounterInterface::class, $this->observableCounter);
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\ObservableCounter
     */
    public function testClassIsNotFinal(): void
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
