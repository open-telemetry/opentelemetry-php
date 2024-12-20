<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Instana;

final class SpanKind
{
    public const ENTRY = 1;
    public const EXIT = 2;
    public const INTERMEDIATE = 3;
}
