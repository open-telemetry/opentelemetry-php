<?php

declare(strict_types=1);

namespace OpenTelemetry\Metrics;

interface MetricKind
{
    public const COUNTER = 1;

    public const UP_DOWN_COUNTER = 2;

    public const VALUE_RECORDER = 3;

    public const SUM_OBSERVER = 4;

    public const UP_DOWN_SUM_OBSERVER = 4;

    public const VALUE_OBSERVER = 5;

    public const TYPES = [
        self::COUNTER,
        self::UP_DOWN_COUNTER,
        self::VALUE_RECORDER,
        self::SUM_OBSERVER,
        self::UP_DOWN_SUM_OBSERVER,
        self::VALUE_OBSERVER,
    ];
}
