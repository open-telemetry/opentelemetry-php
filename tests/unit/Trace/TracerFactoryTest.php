<?php

namespace OpenTelemetry\Tests\Trace;

use Error;
use OpenTelemetry\Context\SpanContext;
use OpenTelemetry\Trace\TracerFactory;
use PHPUnit\Framework\TestCase;

class TracerFactoryTest extends TestCase
{
    /**
     * @test
     * @expectedException Error
     */
    public function shouldNotBeAbleToInstantiateDirectly()
    {
        new TracerFactory();
    }
}