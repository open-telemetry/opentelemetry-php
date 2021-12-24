<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK;

use function hrtime;
use function microtime;

final class SystemClock extends AbstractClock
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
        /**
         * `microtime` returns a unix timestamp _WITH_ microseconds, not _IN_ microseconds.
         * E.g. `1633052992.330921` so we must multiply it by {@see \OpenTelemetry\API\ClockInterface::NANOS_PER_SECOND} to get a value _IN_ nanoseconds.
         */
        return (int) (microtime(true) * ClockInterface::NANOS_PER_SECOND);
    }

    /** @inheritDoc */
    public function nanoTime(): int
    {
        return hrtime(true);
    }
}
