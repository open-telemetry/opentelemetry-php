<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Configuration;

use OpenTelemetry\API\Instrumentation\ConfigurationResolverInterface;

class ConfigurationResolver implements ConfigurationResolverInterface
{
    public function has(string $name): bool
    {
        return Configuration::has($name);
    }

    public function getString(string $name): ?string
    {
        return Configuration::getString($name);
    }

    public function getBoolean(string $name): ?bool
    {
        return Configuration::getBoolean($name);
    }

    public function getInt(string $name): ?int
    {
        return Configuration::getInt($name);
    }

    public function getList(string $name): array
    {
        return Configuration::getList($name);
    }
}
