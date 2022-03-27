<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Dev\Compatibility\BC;

use OpenTelemetry\SDK\Common\Dev\Compatibility\Util;
use OpenTelemetry\SDK\Common\Time\ClockFactory;
use OpenTelemetry\SDK\Common\Time\ClockInterface;
use OpenTelemetry\SDK\Common\Time\Util as TimeUtil;

/**
 * @codeCoverageIgnoreStart
 */
abstract class AbstractClock implements ClockInterface
{
    public static function getDefault(): ClockInterface
    {
        Util::triggerMethodDeprecationNotice(
            'OpenTelemetry\SDK\AbstractClock::getDefault',
            'getDefault',
            ClockFactory::class
        );

        return ClockFactory::getDefault();
    }

    public static function setTestClock(?ClockInterface $clock = null): void
    {
        Util::triggerMethodDeprecationNotice(
            'OpenTelemetry\SDK\AbstractClock::setTestClock',
            'setDefault',
            ClockFactory::class
        );

        ClockFactory::setDefault($clock);
    }

    public static function nanosToMicro(int $nanoseconds): int
    {
        Util::triggerMethodDeprecationNotice(
            'OpenTelemetry\SDK\AbstractClock::nanosToMicro',
            'nanosToMicros',
            TimeUtil::class
        );

        return TimeUtil::nanosToMicros($nanoseconds);
    }

    public static function nanosToMilli(int $nanoseconds): int
    {
        Util::triggerMethodDeprecationNotice(
            'OpenTelemetry\SDK\AbstractClock::nanosToMilli',
            'nanosToMillis',
            TimeUtil::class
        );

        return TimeUtil::nanosToMillis($nanoseconds);
    }

    public static function secondsToNanos(int $seconds): int
    {
        Util::triggerMethodDeprecationNotice(
            'OpenTelemetry\SDK\AbstractClock::secondsToNanos',
            'nanosToMillis',
            TimeUtil::class
        );

        return TimeUtil::nanosToMillis($seconds);
    }
}

/**
 * BC class alias
 * @todo: remove in future release. Also in composer.json autoload/files.
 */
class_alias(AbstractClock::class, 'OpenTelemetry\SDK\AbstractClock');
