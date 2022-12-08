<?php

declare(strict_types=1);
\OpenTelemetry\SDK\Registry::registerSpanExporterFactory('console', \OpenTelemetry\SDK\Trace\SpanExporter\ConsoleSpanExporterFactory::class);
\OpenTelemetry\SDK\Registry::registerSpanExporterFactory('memory', \OpenTelemetry\SDK\Trace\SpanExporter\InMemorySpanExporterFactory::class);

\OpenTelemetry\SDK\Registry::registerTransportFactory('stream', \OpenTelemetry\SDK\Common\Export\Stream\StreamTransportFactory::class);
