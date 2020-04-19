<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Internal;

/**
 * Class Time
 * Base class for Duration and Timestamp.
 * @package OpenTelemetry\Sdk\Internal
 */
abstract class Time
{
    public const NANOSECOND = 1;
    public const MICROSECOND = Time::NANOSECOND * 1000;
    public const MILLISECOND = Time::MICROSECOND * 1000;
    public const SECOND = Time::MILLISECOND * 1000;

    /**
     * @var int Time value in nanoseconds
     */
    protected $time;

    protected function __construct(int $time)
    {
        $this->time = $time;
    }

    /**
     * @param int $resolution Resolution type constant.
     * @return int Integer value of time in appropriate resolution.
     */
    public function to(int $resolution): int
    {
        switch ($resolution) {
            case Time::NANOSECOND:
                return $this->time;
            case Time::MICROSECOND:
            case Time::MILLISECOND:
            case Time::SECOND:
                return (int) (round($this->time / $resolution));
            default:
                throw new \InvalidArgumentException('Invalid resolution');
        }
    }
}
