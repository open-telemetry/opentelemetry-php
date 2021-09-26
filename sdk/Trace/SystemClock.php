<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use function hrtime;
use function microtime;

final class SystemClock extends Clock
{
    private const NANOSECONDS_PER_SECOND = 1000000000;

    private static ?self $instance = null;

    public static function getInstance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /** @inheritDoc */
    public function now(): int
    {
        return (int) (microtime(true) * self::NANOSECONDS_PER_SECOND);
    }

    /** @inheritDoc */
    public function nanoTime(): int
    {
        return hrtime(true);
    }
}
