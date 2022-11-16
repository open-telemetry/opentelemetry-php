<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanExporter;

use OpenTelemetry\SDK\Common\Export\Stream\StreamTransport;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;

class ConsoleSpanExporterFactory implements SpanExporterFactoryInterface
{
    public function fromEnvironment(): SpanExporterInterface
    {
        $transport = new StreamTransport(fopen('php://stdout', 'w'), 'application/json');

        return new ConsoleSpanExporter($transport);
    }
}
