<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Support;

use OpenTelemetry\Sdk\Trace\Tracer;
use OpenTelemetry\Sdk\Trace\TracerProvider;

trait HasTraceProvider
{
    protected function getTracer(string $name = 'OpenTelemetry.TracerTest'): Tracer
    {
        return (new TracerProvider())->getTracer($name);
    }
}
