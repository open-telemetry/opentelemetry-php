<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Zipkin;

final class SpanKind
{
    public const SERVER = 'SERVER';
    public const CLIENT = 'CLIENT';
    public const PRODUCER = 'PRODUCER';
    public const CONSUMER = 'CONSUMER';
}
