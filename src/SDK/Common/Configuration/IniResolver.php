<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Configuration;

/**
 * @interal
 */
class IniResolver extends Resolver
{
    public function retrieveValue(string $variableName, ?string $default = ''): ?string
    {
        return get_cfg_var($variableName) ?: $default;
    }

    public function hasVariable(string $variableName): bool
    {
        return get_cfg_var($variableName) !== false;
    }
}
