<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics;

use OpenTelemetry\SDK\Metrics\MetricRegistry\MetricWriterInterface;
use OpenTelemetry\SDK\Metrics\ObservableCallback;
use OpenTelemetry\SDK\Metrics\ObservableCallbackDestructor;
use OpenTelemetry\SDK\Metrics\ReferenceCounterInterface;
use PHPUnit\Framework\TestCase;

class ObservableCallbackTest extends TestCase
{
    private $mockWriter;
    private $mockReferenceCounter;
    private $mockCallbackDestructor;
    private $target;

    protected function setUp(): void
    {
        $this->mockWriter = $this->createMock(MetricWriterInterface::class);
        $this->mockReferenceCounter = $this->createMock(ReferenceCounterInterface::class);
        $this->mockCallbackDestructor = $this->createMock(ObservableCallbackDestructor::class);
        $this->target = new \stdClass();
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\ObservableCallback::__construct
     * @covers \OpenTelemetry\SDK\Metrics\ObservableCallback::detach
     */
    public function testDetachWithCallbackId(): void
    {
        $callbackId = 123;
        
        $this->mockWriter->expects($this->once())
            ->method('unregisterCallback')
            ->with($callbackId);
            
        $this->mockReferenceCounter->expects($this->once())
            ->method('release');
            
        $callback = new ObservableCallback(
            $this->mockWriter,
            $this->mockReferenceCounter,
            $callbackId,
            $this->mockCallbackDestructor,
            $this->target
        );
        
        $callback->detach();
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\ObservableCallback::__construct
     * @covers \OpenTelemetry\SDK\Metrics\ObservableCallback::detach
     */
    public function testDetachWithoutCallbackId(): void
    {
        $this->mockWriter->expects($this->never())
            ->method('unregisterCallback');
            
        $this->mockReferenceCounter->expects($this->never())
            ->method('release');
            
        $callback = new ObservableCallback(
            $this->mockWriter,
            $this->mockReferenceCounter,
            null,
            $this->mockCallbackDestructor,
            $this->target
        );
        
        $callback->detach();
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\ObservableCallback::__construct
     * @covers \OpenTelemetry\SDK\Metrics\ObservableCallback::detach
     */
    public function testDetachWithCallbackDestructor(): void
    {
        $callbackId = 123;
        
        // Create an ArrayAccess mock for destructors
        $destructorsMock = $this->createMock(\ArrayAccess::class);
        $destructorsMock->expects($this->once())
            ->method('offsetUnset')
            ->with($this->target);
        
        // Create a callback destructor with proper ArrayAccess
        $mockWriter = $this->createMock(\OpenTelemetry\SDK\Metrics\MetricRegistry\MetricWriterInterface::class);
        $mockRefCounter = $this->createMock(\OpenTelemetry\SDK\Metrics\ReferenceCounterInterface::class);
        
        $callbackDestructor = new class($destructorsMock, $mockWriter, $mockRefCounter) extends ObservableCallbackDestructor {
            public function __construct(\ArrayAccess $destructors, $mockWriter, $mockRefCounter) {
                parent::__construct($destructors, $mockWriter);
                $this->callbackIds = [123 => $mockRefCounter];
            }
        };
        
        $this->mockWriter->expects($this->once())
            ->method('unregisterCallback')
            ->with($callbackId);
            
        $this->mockReferenceCounter->expects($this->once())
            ->method('release');
            
        $callback = new ObservableCallback(
            $this->mockWriter,
            $this->mockReferenceCounter,
            $callbackId,
            $callbackDestructor,
            $this->target
        );
        
        $callback->detach();
        
        // Verify the callbackId was removed
        $this->assertArrayNotHasKey($callbackId, $callbackDestructor->callbackIds);
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\ObservableCallback::__construct
     * @covers \OpenTelemetry\SDK\Metrics\ObservableCallback::detach
     */
    public function testDetachWithCallbackDestructorMultipleCallbacks(): void
    {
        $callbackId = 123;
        $callbackId2 = 456;
        
        // Create an ArrayAccess mock for destructors that should NOT be called
        $destructorsMock = $this->createMock(\ArrayAccess::class);
        $destructorsMock->expects($this->never())
            ->method('offsetUnset');
        
        // Create a callback destructor with proper ArrayAccess
        $mockWriter = $this->createMock(\OpenTelemetry\SDK\Metrics\MetricRegistry\MetricWriterInterface::class);
        $mockRefCounter1 = $this->createMock(\OpenTelemetry\SDK\Metrics\ReferenceCounterInterface::class);
        $mockRefCounter2 = $this->createMock(\OpenTelemetry\SDK\Metrics\ReferenceCounterInterface::class);
        
        $callbackDestructor = new class($destructorsMock, $mockWriter, $mockRefCounter1, $mockRefCounter2) extends ObservableCallbackDestructor {
            public function __construct(\ArrayAccess $destructors, $mockWriter, $mockRefCounter1, $mockRefCounter2) {
                parent::__construct($destructors, $mockWriter);
                $this->callbackIds = [
                    123 => $mockRefCounter1, 
                    456 => $mockRefCounter2
                ];
            }
        };
        
        $this->mockWriter->expects($this->once())
            ->method('unregisterCallback')
            ->with($callbackId);
            
        $this->mockReferenceCounter->expects($this->once())
            ->method('release');
            
        $callback = new ObservableCallback(
            $this->mockWriter,
            $this->mockReferenceCounter,
            $callbackId,
            $callbackDestructor,
            $this->target
        );
        
        $callback->detach();
        
        // Verify the callbackId was removed
        $this->assertArrayNotHasKey($callbackId, $callbackDestructor->callbackIds);
        // Verify the target was NOT removed since there are still other callbackIds
        $this->assertArrayHasKey($callbackId2, $callbackDestructor->callbackIds);
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\ObservableCallback::__construct
     * @covers \OpenTelemetry\SDK\Metrics\ObservableCallback::detach
     */
    public function testDetachWithoutCallbackDestructor(): void
    {
        $callbackId = 123;
        
        $this->mockWriter->expects($this->once())
            ->method('unregisterCallback')
            ->with($callbackId);
            
        $this->mockReferenceCounter->expects($this->once())
            ->method('release');
            
        $callback = new ObservableCallback(
            $this->mockWriter,
            $this->mockReferenceCounter,
            $callbackId,
            null,
            $this->target
        );
        
        $callback->detach();
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\ObservableCallback::__construct
     * @covers \OpenTelemetry\SDK\Metrics\ObservableCallback::detach
     */
    public function testDetachResetsCallbackIdAndTarget(): void
    {
        $callbackId = 123;
        
        $callback = new ObservableCallback(
            $this->mockWriter,
            $this->mockReferenceCounter,
            $callbackId,
            null,
            $this->target
        );
        
        // Use reflection to check private properties
        $reflection = new \ReflectionClass($callback);
        $callbackIdProp = $reflection->getProperty('callbackId');
        $targetProp = $reflection->getProperty('target');
        $callbackIdProp->setAccessible(true);
        $targetProp->setAccessible(true);
        
        $callback->detach();
        
        $this->assertNull($callbackIdProp->getValue($callback));
        $this->assertNull($targetProp->getValue($callback));
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\ObservableCallback::__construct
     * @covers \OpenTelemetry\SDK\Metrics\ObservableCallback::__destruct
     */
    public function testDestructorWithCallbackDestructor(): void
    {
        $callbackId = 123;
        
        $this->mockReferenceCounter->expects($this->never())
            ->method('acquire');
            
        $this->mockReferenceCounter->expects($this->never())
            ->method('release');
            
        $callback = new ObservableCallback(
            $this->mockWriter,
            $this->mockReferenceCounter,
            $callbackId,
            $this->mockCallbackDestructor,
            $this->target
        );
        
        // Trigger destructor
        unset($callback);
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\ObservableCallback::__construct
     * @covers \OpenTelemetry\SDK\Metrics\ObservableCallback::__destruct
     */
    public function testDestructorWithoutCallbackDestructor(): void
    {
        $callbackId = 123;
        
        $this->mockReferenceCounter->expects($this->once())
            ->method('acquire')
            ->with(true);
            
        $this->mockReferenceCounter->expects($this->once())
            ->method('release');
            
        $callback = new ObservableCallback(
            $this->mockWriter,
            $this->mockReferenceCounter,
            $callbackId,
            null,
            $this->target
        );
        
        // Trigger destructor
        unset($callback);
    }

    /**
     * @covers \OpenTelemetry\SDK\Metrics\ObservableCallback::__construct
     * @covers \OpenTelemetry\SDK\Metrics\ObservableCallback::__destruct
     */
    public function testDestructorWithoutCallbackId(): void
    {
        $this->mockReferenceCounter->expects($this->never())
            ->method('acquire');
            
        $this->mockReferenceCounter->expects($this->never())
            ->method('release');
            
        $callback = new ObservableCallback(
            $this->mockWriter,
            $this->mockReferenceCounter,
            null,
            null,
            $this->target
        );
        
        // Trigger destructor
        unset($callback);
    }
}
