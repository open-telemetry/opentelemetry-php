<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Configuration;

use function in_array;

/**
 * @internal
 */
final class EnvResolver implements \OpenTelemetry\API\Configuration\ConfigEnv\EnvResolver
{
    #[\Override]
    public function string(string $name): ?string
    {
        if (!Configuration::has($name)) {
            return null;
        }

        return Configuration::getString($name);
    }

    #[\Override]
    public function enum(string $name, array $values): ?string
    {
        if (!Configuration::has($name)) {
            return null;
        }

        $value = Configuration::getEnum($name);
        if (!in_array($value, $values, true)) {
            return null;
        }

        return $value;
    }

    #[\Override]
    public function bool(string $name): ?bool
    {
        if (!Configuration::has($name)) {
            return null;
        }

        return Configuration::getBoolean($name);
    }

    #[\Override]
    public function int(string $name, ?int $min = 0, ?int $max = ~(-1 << 31)): int|null
    {
        if (!Configuration::has($name)) {
            return null;
        }

        $value = Configuration::getInt($name);
        if ($value < $min || $value > $max) {
            return null;
        }

        return $value;
    }

    #[\Override]
    public function numeric(string $name, float|int|null $min = 0, float|int|null $max = ~(-1 << 31)): float|int|null
    {
        if (!Configuration::has($name)) {
            return null;
        }

        $value = Configuration::getFloat($name);
        if ($value < $min || $value > $max) {
            return null;
        }

        return $value;
    }

    #[\Override]
    public function list(string $name): ?array
    {
        if (!Configuration::has($name)) {
            return null;
        }

        /** @var list<string> $value */
        $value = Configuration::getList($name);

        return $value;
    }

    #[\Override]
    public function map(string $name): ?array
    {
        if (!Configuration::has($name)) {
            return null;
        }

        return Configuration::getMap($name);
    }
}
