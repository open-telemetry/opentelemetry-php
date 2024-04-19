<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Time;

use OpenTelemetry\API\Common\Time\ClockInterface;

/**
 * @deprecated Use OpenTelemetry\API\Common\Time\ClockFactory
 * @codeCoverageIgnore
 */
class ClockFactory
{
    public static function getDefault(): ClockInterface
    {
        return \OpenTelemetry\API\Common\Time\ClockFactory::getDefault();
    }
}
