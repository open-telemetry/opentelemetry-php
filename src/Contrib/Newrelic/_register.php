<?php

declare(strict_types=1);

\OpenTelemetry\SDK\FactoryRegistry::registerSpanExporterFactory('newrelic', \OpenTelemetry\Contrib\Newrelic\SpanExporterFactory::class);
