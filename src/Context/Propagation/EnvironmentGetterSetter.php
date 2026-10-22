<?php

declare(strict_types=1);

namespace OpenTelemetry\Context\Propagation;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.61.0/specification/context/env-carriers.md
 *
 * Default implementation of {@see ExtendedPropagationGetterInterface} and {@see PropagationSetterInterface}.
 * This type uses environment variables as a carrier for context propagation.
 * It is provided to {@see TextMapPropagatorInterface::inject()} or {@see TextMapPropagatorInterface::extract()}.
 *
 * Keys are normalized to environment variable names, so `traceparent` becomes `TRACEPARENT`.
 * The carrier argument is ignored: values are read from and written to the environment of the
 * current process, which child processes started afterwards inherit.
 */
final class EnvironmentGetterSetter implements ExtendedPropagationGetterInterface, PropagationSetterInterface
{
    private const NORMALIZED = '/^[A-Z_][A-Z0-9_]*$/';

    private static ?self $instance = null;

    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    #[\Override]
    public function keys($carrier): array
    {
        $keys = [];
        foreach (array_keys(getenv()) as $key) {
            $key = (string) $key;
            if (preg_match(self::NORMALIZED, $key)) {
                $keys[] = $key;
            }
        }

        return $keys;
    }

    #[\Override]
    public function get($carrier, string $key): ?string
    {
        $value = getenv(self::normalize($key));

        return is_string($value) ? $value : null;
    }

    #[\Override]
    public function getAll($carrier, string $key): array
    {
        $value = $this->get($carrier, $key);

        return $value !== null ? [$value] : [];
    }

    #[\Override]
    public function set(&$carrier, string $key, string $value): void
    {
        putenv(sprintf('%s=%s', self::normalize($key), $value));
    }

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.61.0/specification/context/env-carriers.md#key-name-normalization
     */
    private static function normalize(string $key): string
    {
        if ($key === '') {
            return '_';
        }
        $key = strtoupper($key);
        $key = preg_replace('/[^A-Z0-9_]/u', '_', $key)
            // not valid UTF-8, so replace byte by byte
            ?? (string) preg_replace('/[^A-Z0-9_]/', '_', $key);
        if (ctype_digit($key[0])) {
            $key = '_' . $key;
        }

        return $key;
    }
}
