<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\SDK\Common\InstrumentationScope\Config;
use OpenTelemetry\SDK\Common\InstrumentationScope\ConfigTrait;

class MeterConfig implements Config
{
    use ConfigTrait;

    public static function default(): self
    {
        return new self();
    }
}
