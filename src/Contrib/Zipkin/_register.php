<?php

declare(strict_types=1);

\OpenTelemetry\SDK\Registry::registerSpanExporterFactory('zipkin', \OpenTelemetry\Contrib\Zipkin\SpanExporterFactory::class);
