<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanExporter;

use OpenTelemetry\SDK\Registry;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;

class ConsoleSpanExporterFactory implements SpanExporterFactoryInterface
{
    #[\Override]
    public function create(): SpanExporterInterface
    {
        $transport = Registry::transportFactory('stream')->create('php://stdout', 'application/json');

        return new ConsoleSpanExporter($transport);
    }
}
