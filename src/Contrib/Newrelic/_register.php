<?php

declare(strict_types=1);

\OpenTelemetry\SDK\Registry::registerSpanExporterFactory('newrelic', \OpenTelemetry\Contrib\Newrelic\SpanExporterFactory::class);
