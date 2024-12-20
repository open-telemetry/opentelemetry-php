<?php

declare(strict_types=1);

\OpenTelemetry\SDK\Registry::registerSpanExporterFactory('instana', \OpenTelemetry\Contrib\Instana\SpanExporterFactory::class);
