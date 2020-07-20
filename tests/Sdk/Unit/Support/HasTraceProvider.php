<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Support;

use OpenTelemetry\Sdk\Trace\TracerProvider;
use OpenTelemetry\Sdk\Trace\Tracer;

trait HasTraceProvider
{
    protected function getTracer(string $name = 'OpenTelemetry.TracerTest'): Tracer
    {
        return (new TracerProvider())->getTracer($name);
    }
}
