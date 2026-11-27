<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Configuration;

use function in_array;
use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use function sprintf;

/**
 * @internal
 */
final class EnvResolver implements \OpenTelemetry\API\Configuration\ConfigEnv\EnvResolver
{
    use LogsMessagesTrait;

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
        $value = Configuration::getNullableInt($name);

        return $value === null ? null : self::inRange($name, $value, $min, $max);
    }

    #[\Override]
    public function numeric(string $name, float|int|null $min = 0, float|int|null $max = ~(-1 << 31)): float|int|null
    {
        $value = Configuration::getNullableFloat($name);

        return $value === null ? null : self::inRange($name, $value, $min, $max);
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

    /**
     * @template T of int|float
     * @param T $value
     * @return T|null
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.62.0/specification/configuration/common.md#numeric
     */
    private static function inRange(string $name, int|float $value, int|float|null $min, int|float|null $max): int|float|null
    {
        if (($min !== null && $value < $min) || ($max !== null && $value > $max)) {
            self::logWarning(sprintf('Out of range value "%s" for %s, ignoring it', $value, $name));

            return null;
        }

        return $value;
    }
}
