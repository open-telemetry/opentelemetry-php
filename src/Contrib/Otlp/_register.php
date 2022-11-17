<?php

declare(strict_types=1);
\OpenTelemetry\SDK\Registry::registerSpanExporterFactory('otlp', \OpenTelemetry\Contrib\Otlp\SpanExporterFactory::class);
\OpenTelemetry\SDK\Registry::registerMetricExporterFactory('otlp', \OpenTelemetry\Contrib\Otlp\MetricExporterFactory::class);

\OpenTelemetry\SDK\Registry::registerTransportFactory('http/protobuf', \OpenTelemetry\Contrib\Otlp\OtlpHttpTransportFactory::class);
\OpenTelemetry\SDK\Registry::registerTransportFactory('http/json', \OpenTelemetry\Contrib\Otlp\OtlpHttpTransportFactory::class);
\OpenTelemetry\SDK\Registry::registerTransportFactory('http/ndjson', \OpenTelemetry\Contrib\Otlp\OtlpHttpTransportFactory::class);
