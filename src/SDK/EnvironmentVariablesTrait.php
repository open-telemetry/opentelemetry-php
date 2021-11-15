<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK;

/**
 * Centralized methods for retrieving environment variables
 */
trait EnvironmentVariablesTrait
{
    /**
     * Retrieve an integer value from an environment variable
     */
    public function getIntFromEnvironment(string $key, int $default): int
    {
        $value = getenv($key);
        if (false === $value || '' === $value) {
            return $default;
        }
        if (false === \filter_var($value, FILTER_VALIDATE_INT)) {
            throw new \InvalidArgumentException($key . ' contains non-numeric value');
        }

        return (int) $value;
    }

    public function getStringFromEnvironment(string $key, string $default): string
    {
        $value = getenv($key);
        if (false === $value || '' === $value) {
            return $default;
        }

        return $value;
    }

    public function getBooleanFromEnvironment(string $key, bool $default): bool
    {
        $value = getenv($key);
        if (false === $value || '' === $value) {
            return $default;
        }
        switch (strtolower($value)) {
            case 'true':
            case '1':
                return true;
            case 'false':
            case '0':
                return false;
            default:
                throw new \InvalidArgumentException($key . ' contains a non-boolean value');
        }
    }
}
