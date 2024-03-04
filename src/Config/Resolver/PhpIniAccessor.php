<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\Resolver;

class PhpIniAccessor
{
    /**
     * Mockable accessor for php.ini values
     * @internal
     * @return array|false|string
     */
    public function get(string $variableName)
    {
        return get_cfg_var($variableName);
    }
}
