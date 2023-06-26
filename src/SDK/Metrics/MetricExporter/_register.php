<?php

declare(strict_types=1);

\OpenTelemetry\SDK\Registry::registerMetricExporterFactory('memory', \OpenTelemetry\SDK\Metrics\MetricExporter\InMemoryExporterFactory::class);
\OpenTelemetry\SDK\Registry::registerMetricExporterFactory('console', \OpenTelemetry\SDK\Metrics\MetricExporter\ConsoleMetricExporterFactory::class);
\OpenTelemetry\SDK\Registry::registerMetricExporterFactory('none', \OpenTelemetry\SDK\Metrics\MetricExporter\NoopMetricExporterFactory::class);
