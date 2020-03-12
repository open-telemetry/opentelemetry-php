<?php

declare(strict_types=1);

namespace OpenTelemetry\Trace;

interface SpanKind
{
    const KIND_INTERNAL = 0;
    const KIND_CLIENT = 1;
    const KIND_SERVER = 2;
    const KIND_PRODUCER = 3;
    const KIND_CONSUMER = 4;

    public function getSpanKind(): int;
}
