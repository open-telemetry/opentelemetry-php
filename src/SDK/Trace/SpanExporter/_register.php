<?php

declare(strict_types=1);
\OpenTelemetry\SDK\FactoryRegistry::registerSpanExporterFactory('console', \OpenTelemetry\SDK\Trace\SpanExporter\ConsoleSpanExporterFactory::class);
\OpenTelemetry\SDK\FactoryRegistry::registerSpanExporterFactory('memory', \OpenTelemetry\SDK\Trace\SpanExporter\InMemorySpanExporterFactory::class);
