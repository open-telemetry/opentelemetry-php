<?php

declare(strict_types=1);
\OpenTelemetry\SDK\FactoryRegistry::registerSpanExporterFactory('otlp', \OpenTelemetry\Contrib\Otlp\SpanExporterFactory::class);
\OpenTelemetry\SDK\FactoryRegistry::registerMetricExporterFactory('otlp', \OpenTelemetry\Contrib\Otlp\MetricExporterFactory::class);

\OpenTelemetry\SDK\FactoryRegistry::registerTransportFactory('http/protobuf', \OpenTelemetry\Contrib\Otlp\OtlpHttpTransportFactory::class);
\OpenTelemetry\SDK\FactoryRegistry::registerTransportFactory('http/json', \OpenTelemetry\Contrib\Otlp\OtlpHttpTransportFactory::class);
\OpenTelemetry\SDK\FactoryRegistry::registerTransportFactory('http/ndjson', \OpenTelemetry\Contrib\Otlp\OtlpHttpTransportFactory::class);
