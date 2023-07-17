<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Instrumentation;

class NoopConfigurationResolver implements ConfigurationResolverInterface
{
    public function has(string $name): bool
    {
        return false;
    }

    public function getString(string $name): ?string
    {
        return null;
    }

    public function getBoolean(string $name): ?bool
    {
        return null;
    }

    public function getInt(string $name): ?int
    {
        return null;
    }

    public function getList(string $name): array
    {
        return [];
    }
}
