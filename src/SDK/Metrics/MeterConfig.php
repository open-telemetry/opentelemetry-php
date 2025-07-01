<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\SDK\Common\InstrumentationScope\Config;
use OpenTelemetry\SDK\Common\InstrumentationScope\ConfigTrait;

class MeterConfig implements Config
{
    public const SELF_DIAGNOSTICS = 'php.otel.sdk.self-diagnostics';

    use ConfigTrait;

    public static function default(): self
    {
        return new self();
    }
}
