<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Common\Signal;

use InvalidArgumentException;

class Signals
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

    public static function validate(string $signal): void
    {
        if (!in_array($signal, self::SIGNALS)) {
            throw new InvalidArgumentException('Unknown signal: ' . $signal);
        }
    }
}
