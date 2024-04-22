<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\Configuration\Accessor;

/**
 * @internal
 */
class PhpIniAccessor
{
    /**
     * Mockable accessor for php.ini values
     */
    public function get(string $variableName): array|false|string
    {
        return get_cfg_var($variableName);
    }
}
