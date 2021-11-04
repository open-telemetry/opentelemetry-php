<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanExporter;

use OpenTelemetry\SDK\Trace\SpanConverterInterface;
use OpenTelemetry\SDK\Trace\SpanDataInterface;

class NullSpanConverter implements SpanConverterInterface
{
    public function convert(SpanDataInterface $span): array
    {
        return [];
    }
}
