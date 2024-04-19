<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Time;

use OpenTelemetry\API\Common\Time\Util as API;

/**
 * @deprecated Use OpenTelemetry\API\Common\Time\Util
 * @codeCoverageIgnore
 */
class Util
{
    public static function nanosToMicros(int $nanoseconds): int
    {
        return API::nanosToMicros($nanoseconds);
    }

    public static function nanosToMillis(int $nanoseconds): int
    {
        return API::nanosToMillis($nanoseconds);
    }

    public static function secondsToNanos(int $seconds): int
    {
        return API::secondsToNanos($seconds);
    }

    public static function millisToNanos(int $milliSeconds): int
    {
        return API::millisToNanos($milliSeconds);
    }
}
