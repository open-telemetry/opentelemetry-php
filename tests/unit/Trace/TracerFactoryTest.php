<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Trace;

use Error;
use OpenTelemetry\Sdk\Trace\TracerFactory;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use StdClass;

class TracerFactoryTest extends TestCase
{
    public function tearDown()
    {
        // since a singleton is tested we need to reset instance after every test
        $refProperty = new ReflectionProperty(TracerFactory::class, 'instance');
        $refProperty->setAccessible(true);
        $refProperty->setValue(null);
    }

    /**
     * @test
     * @expectedException Error
     */
    public function shouldNotBeAbleToInstantiateDirectly()
    {
        new TracerFactory();
    }

    /**
     * @test
     */
    public function gettingSameTracerMultipleTimesShouldReturnSameObject()
    {
        $tracer1 = TracerFactory::getInstance()->getTracer('test_tracer');
        $tracer2 = TracerFactory::getInstance()->getTracer('test_tracer');

        $this->assertTrue($tracer1 === $tracer2);
    }

    /**
     * @test
     */
    public function callingSameInstanceMultipleTimesShouldReturnSameFactoryObject()
    {
        $instance1 = TracerFactory::getInstance();
        $instance2 = TracerFactory::getInstance();

        $this->assertTrue($instance1 === $instance2);
    }

    /**
     * @test
     */
    public function shouldInstantiateWithoutErrorIfConfigurationIsOk()
    {
        $factory = TracerFactory::getInstance();

        $this->assertInstanceOf(TracerFactory::class, $factory);
    }

    /**
     * @test
     * @dataProvider wrongConfigurationDataProvider
     * @expectedException \TypeError
     */
    public function shouldThrowExceptionIfConfigurationParamsAreInvalid($spanProcessors)
    {
        TracerFactory::getInstance($spanProcessors);
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
