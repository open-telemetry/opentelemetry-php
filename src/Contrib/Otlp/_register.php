<?php

declare(strict_types=1);
\OpenTelemetry\SDK\FactoryRegistry::registerSpanExporterFactory('otlp', \OpenTelemetry\Contrib\Otlp\SpanExporterFactory::class);
\OpenTelemetry\SDK\FactoryRegistry::registerMetricExporterFactory('otlp', \OpenTelemetry\Contrib\Otlp\MetricExporterFactory::class);

\OpenTelemetry\SDK\FactoryRegistry::registerTransportFactory('http', \OpenTelemetry\Contrib\Otlp\OtlpHttpTransportFactory::class);
