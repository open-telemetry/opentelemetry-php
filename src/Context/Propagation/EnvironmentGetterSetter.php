<?php

declare(strict_types=1);

namespace OpenTelemetry\Context\Propagation;

use InvalidArgumentException;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.44.0/specification/context/env-carriers.md
 *
 * Default implementation of {@see ExtendedPropagationGetterInterface} and {@see PropagationSetterInterface}.
 * This type uses environment variables as a carrier for context propagation.
 * It is provided to {@see TextMapPropagatorInterface::inject()} or {@see TextMapPropagatorInterface::extract()}.
 */
final class EnvironmentGetterSetter implements ExtendedPropagationGetterInterface, PropagationSetterInterface
{
    private static ?self $instance = null;

    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    #[\Override]
    public function keys($carrier): array
    {
        $envs = getenv();
        if (!is_array($envs) || $envs === []) {
            return [];
        }

        return array_map('strtolower', array_keys($envs));
    }

    #[\Override]
    public function get($carrier, string $key): ?string
    {
        $value = getenv(strtoupper($key));

        return is_string($value) ? $value : null;
    }

    #[\Override]
    public function getAll($carrier, string $key): array
    {
        $value = getenv(strtoupper($key));

        return is_string($value) ? [$value] : [];
    }

    #[\Override]
    public function set(&$carrier, string $key, string $value): void
    {
        if ($key === '') {
            throw new InvalidArgumentException('Unable to set value with an empty key');
        }

        putenv(sprintf('%s=%s', strtoupper($key), $value));
    }
}
