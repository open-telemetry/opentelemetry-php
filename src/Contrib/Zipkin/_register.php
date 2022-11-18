<?php

declare(strict_types=1);

\OpenTelemetry\SDK\FactoryRegistry::registerSpanExporterFactory('zipkin', \OpenTelemetry\Contrib\Zipkin\SpanExporterFactory::class);
