<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Time;

use OpenTelemetry\API\Common\Time as API;

/**
 * @deprecated Use OpenTelemetry\API\Common\Time\ClockFactory
 * @codeCoverageIgnore
 */
class ClockFactory implements API\ClockFactoryInterface
{
    public static function getDefault(): API\ClockInterface
    {
        return API\ClockFactory::getDefault();
    }

    public static function create(): API\ClockFactoryInterface
    {
        return API\ClockFactory::create();
    }

    public function build(): API\ClockInterface
    {
        return (new API\ClockFactory())->build();
    }

    public static function setDefault(?API\ClockInterface $clock): void
    {
        API\ClockFactory::setDefault($clock);
    }
}
