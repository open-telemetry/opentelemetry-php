<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Data;

final class Temporality
{
    public const DELTA = 'Delta';
    public const CUMULATIVE = 'Cumulative';
}
