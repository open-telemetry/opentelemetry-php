<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs;

use OpenTelemetry\SDK\Common\InstrumentationScope\Config;
use OpenTelemetry\SDK\Common\InstrumentationScope\ConfigTrait;

class LoggerConfig implements Config
{
    use ConfigTrait;

    public static function default(): self
    {
        return new self();
    }
}
