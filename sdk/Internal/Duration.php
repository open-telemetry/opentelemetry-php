<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Internal;

use OpenTelemetry\Trace as API;

final class Duration extends Time
{
    /**
     * @param int $period Duration period in nanoseconds
     * @return Duration
     */
    public static function of(int $period): Duration
    {
        return new Duration($period);
    }

    /**
     * @param Timestamp|API\Timestamp $start
     * @param Timestamp|API\Timestamp $end
     * @return Duration
     */
    public static function between(API\Timestamp $start, API\Timestamp $end): Duration
    {
        // todo: check the duration is the positive number, if it's necessary
        return $end->sub($start);
    }
}
