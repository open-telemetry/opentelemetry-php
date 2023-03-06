<?php

declare(strict_types=1);

\OpenTelemetry\SDK\Registry::registerLogRecordExporterFactory('console', \OpenTelemetry\SDK\Logs\Exporter\ConsoleExporterFactory::class);
