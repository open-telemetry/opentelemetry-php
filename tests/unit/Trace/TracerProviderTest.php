<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Tests;

use OpenTelemetry\Sdk\Trace\TracerProvider;
use PHPUnit\Framework\TestCase;

class TracerProviderTest extends TestCase
{

    /**
     * @test
     */
    public function gettingSameTracerMultipleTimesShouldReturnSameObject()
    {
        $traceProvider = new TracerProvider();
        $tracer1 = $traceProvider->getTracer('test_tracer');
        $tracer2 = $traceProvider->getTracer('test_tracer');

        self::assertSame($tracer1, $tracer2);
    }
}
