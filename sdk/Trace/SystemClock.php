<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use function hrtime;
use function microtime;
use OpenTelemetry\Trace as API;

final class SystemClock extends Clock
{
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
        return (int) (microtime(true) * API\Clock::NANOS_PER_SECOND);
    }

    /** @inheritDoc */
    public function nanoTime(): int
    {
        return hrtime(true);
    }
}
