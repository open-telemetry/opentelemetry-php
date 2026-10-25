<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Zipkin;

/**
 * @deprecated The Zipkin exporter is deprecated. Use OTLP instead.
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.61.0/specification/trace/sdk_exporters/zipkin.md
 */
final class SpanKind
{
    public const SERVER = 'SERVER';
    public const CLIENT = 'CLIENT';
    public const PRODUCER = 'PRODUCER';
    public const CONSUMER = 'CONSUMER';
}
