<?php

declare(strict_types=1);

\OpenTelemetry\SDK\FactoryRegistry::registerMetricExporterFactory('memory', \OpenTelemetry\SDK\Metrics\MetricExporter\InMemoryExporterFactory::class);
\OpenTelemetry\SDK\FactoryRegistry::registerMetricExporterFactory('none', \OpenTelemetry\SDK\Metrics\MetricExporter\NoopMetricExporterFactory::class);
