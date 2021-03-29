<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

interface IdGenerator
{
    public function generateTraceId(): string;

    public function generateSpanId(): string;
}
