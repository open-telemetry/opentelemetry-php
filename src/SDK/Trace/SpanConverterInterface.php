<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

interface SpanConverterInterface
{
    public function convert(SpanDataInterface $span): array;
}
