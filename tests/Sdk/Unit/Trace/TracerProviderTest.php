<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Trace;

use OpenTelemetry\Sdk\Trace\TracerProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use StdClass;
use TypeError;

class TracerProviderTest extends TestCase
{
    public function tearDown(): void
    {
        // since a singleton is tested we need to reset instance after every test
        $refProperty = new ReflectionProperty(TracerProvider::class, 'instance');
        $refProperty->setAccessible(true);
        $refProperty->setValue(null);
    }

    public function testShouldNotBeAbleToInstantiateDirectly()
    {
        $this->expectException(ReflectionException::class);
        // use reflection to silence phan warning about calling private constructor
        $class = new ReflectionClass(TracerProvider::class);
        $class->newInstance();
    }

    /**
     * @test
     */
    public function gettingSameTracerMultipleTimesShouldReturnSameObject()
    {
        $traceProvider = TracerProvider::getInstance();
        $tracer1 = $traceProvider->getTracer('test_tracer');
        $tracer2 = $traceProvider->getTracer('test_tracer');

        self::assertSame($tracer1, $tracer2);
    }

    /**
     * @test
     */
    public function callingSameInstanceMultipleTimesShouldReturnSameFactoryObject()
    {
        $instance1 = TracerProvider::getInstance();
        $instance2 = TracerProvider::getInstance();

        self::assertSame($instance1, $instance2);
    }

    /**
     * @dataProvider wrongConfigurationDataProvider
     */
    public function testShouldThrowExceptionIfConfigurationParamsAreInvalid($spanProcessors)
    {
        $this->expectException(TypeError::class);
        TracerProvider::getInstance($spanProcessors);
    }

    public function wrongConfigurationDataProvider()
    {
        return [
            'array of numbers' => [
                'spanProcessors' => [1, -1, 0.1, -0.1, 0],
            ],
            'array of strings' => [
                'spanProcessors' => ['aaa', 'bbb', ''],
            ],
            'array of standardObjects' => [
                'spanProcessors' => [new StdClass(), new StdClass()],
            ],
            'array of boolean' => [
                'spanProcessors' => [true, false, null],
            ],
        ];
    }
}
