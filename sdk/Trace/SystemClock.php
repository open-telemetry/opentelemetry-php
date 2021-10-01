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
        /**
         * `microtime` returns a unix timestamp _WITH_ microseconds, not _IN_ microseconds.
         * E.g. `1633052992.330921` so we must multiply it by {@see API\Clock::NANOS_PER_SECOND} to get a value _IN_ nanoseconds.
         */
        return (int) (microtime(true) * API\Clock::NANOS_PER_SECOND);
    }

    /** @inheritDoc */
    public function nanoTime(): int
    {
        return hrtime(true);
    }
}
