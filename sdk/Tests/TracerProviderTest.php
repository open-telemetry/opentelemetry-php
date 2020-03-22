<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Tests;

use Error;
use OpenTelemetry\Sdk\Trace\TracerProvider;
use ReflectionProperty;
use StdClass;

class TracerProviderTest extends \PHPUnit\Framework\TestCase
{
    public function tearDown()
    {
        // since a singleton is tested we need to reset instance after every test
        $refProperty = new ReflectionProperty(TracerProvider::class, 'instance');
        $refProperty->setAccessible(true);
        $refProperty->setValue(null);
    }

    /**
     * @test
     * @expectedException Error
     */
    public function shouldNotBeAbleToInstantiateDirectly()
    {
        new TracerProvider();
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
     * @test
     * @dataProvider wrongConfigurationDataProvider
     * @expectedException \TypeError
     */
    public function shouldThrowExceptionIfConfigurationParamsAreInvalid($spanProcessors)
    {
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
