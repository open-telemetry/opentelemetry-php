<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK;

use OpenTelemetry\SDK\Common\Environment\EnvironmentVariables;
use OpenTelemetry\SDK\Common\Environment\Variables;

class SDK
{
    public static function isDisabled(): bool
    {
        return EnvironmentVariables::getBoolean(Variables::OTEL_SDK_DISABLED);
    }
}
