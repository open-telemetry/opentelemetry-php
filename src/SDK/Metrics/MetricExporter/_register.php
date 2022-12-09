<?php

declare(strict_types=1);

\OpenTelemetry\SDK\Registry::registerMetricExporterFactory('memory', \OpenTelemetry\SDK\Metrics\MetricExporter\InMemoryExporterFactory::class);
\OpenTelemetry\SDK\Registry::registerMetricExporterFactory('none', \OpenTelemetry\SDK\Metrics\MetricExporter\NoopMetricExporterFactory::class);
