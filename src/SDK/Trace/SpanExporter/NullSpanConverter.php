<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanExporter;

use OpenTelemetry\SDK\Trace\SpanConverterInterface;

class NullSpanConverter implements SpanConverterInterface
{
    public function convert(iterable $spans): array
    {
        return [[]];
    }
}
