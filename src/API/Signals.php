<?php

declare(strict_types=1);

namespace OpenTelemetry\API;

interface Signals
{
    /** @var string  */
    public const TRACE = 'trace';
    /** @var string  */
    public const METRICS = 'metrics';
    /** @var string  */
    public const LOGS = 'logs';
    /** @var string[]  */
    public const SIGNALS = [
        self::TRACE,
        self::METRICS,
        self::LOGS,
    ];
}
