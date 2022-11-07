<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Configuration\Resolver;

use OpenTelemetry\SDK\Common\Configuration\Resolver;

/**
 * @interal
 * @psalm-suppress TypeDoesNotContainType
 */
class IniResolver extends Resolver
{
    public function retrieveValue(string $variableName, ?string $default = ''): ?string
    {
        $value = get_cfg_var($variableName);
        if (is_array($value)) {
            return implode(',', $value);
        }

        return $value ?: $default;
    }

    public function hasVariable(string $variableName): bool
    {
        return get_cfg_var($variableName) !== false;
    }
}
