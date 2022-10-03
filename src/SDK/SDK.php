<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK;

use OpenTelemetry\SDK\Common\Environment\Accessor;
use OpenTelemetry\SDK\Common\Environment\EnvironmentVariablesTrait;
use OpenTelemetry\SDK\Common\Environment\Variables;

class SDK
{
    use EnvironmentVariablesTrait;

    public static function isDisabled(): bool
    {
        return Accessor::getBool(Variables::OTEL_SDK_DISABLED);
    }
}
