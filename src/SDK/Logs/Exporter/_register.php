<?php

declare(strict_types=1);

\OpenTelemetry\SDK\Registry::registerLogRecordExporterFactory('console', \OpenTelemetry\SDK\Logs\Exporter\ConsoleExporterFactory::class);
\OpenTelemetry\SDK\Registry::registerLogRecordExporterFactory('memory', \OpenTelemetry\SDK\Logs\Exporter\InMemoryExporterFactory::class);
