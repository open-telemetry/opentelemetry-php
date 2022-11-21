<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanExporter;

use OpenTelemetry\SDK\FactoryRegistry;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;

class ConsoleSpanExporterFactory implements SpanExporterFactoryInterface
{
    public function fromEnvironment(): SpanExporterInterface
    {
        $transport = FactoryRegistry::transportFactory('stream')->create('php://stdout', 'application/json');

        return new ConsoleSpanExporter($transport);
    }
}
