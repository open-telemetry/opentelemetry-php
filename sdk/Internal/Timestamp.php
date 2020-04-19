<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Internal;

use OpenTelemetry\Trace as API;

final class Timestamp extends Time implements API\Timestamp
{

    /**
     * @var int|float Monotonic clock timestamp
     * @see https://www.php.net/manual/en/function.hrtime
     */
    private $monotonic;

    /**
     * @return Timestamp Returns OpenTelemetry\Sdk\Internal\Timestamp corresponding to the current local time.
     */
    public static function now(): Timestamp
    {
        $timestamp = new Timestamp((int) (microtime(true) * Time::SECOND));

        // `hrtime` is available in PHP>=7.3
        if (function_exists('hrtime')) {
            // save monotonic timestamp for more precise Duration calculations
            $timestamp->monotonic = \hrtime(true);
        }

        return $timestamp;
    }

    /**
     * @param int $nsec Number of nanoseconds elapsed since January 1, 1970 UTC.
     * @return Timestamp Returns OpenTelemetry\Sdk\Internal\Timestamp corresponding to the given Unix time in nanoseconds.
     */
    public static function at(int $nsec): Timestamp
    {
        return new Timestamp($nsec);
    }

    /**
     * @param Timestamp $t
     * @return Duration Returns the OpenTelemetry\Sdk\Internal\Duration time elapsed since Timestamp `t`
     */
    public function sub(Timestamp $t): Duration
    {
        // both timestamps have monotonic values
        if (null !== $this->monotonic && null !== $t->monotonic) {
            if (is_float($this->monotonic)) {
                // monotonic timestamp is a float number on 32-bit platforms
                return Duration::of((int) (($this->monotonic - $t->monotonic) * Time::SECOND));
            }

            return Duration::of($this->monotonic - $t->monotonic);
        }

        return Duration::of($this->time - $t->time);
    }
}
