<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

interface IdGeneratorInterface
{
    public function generateTraceId(): string;

    public function generateSpanId(): string;
}
