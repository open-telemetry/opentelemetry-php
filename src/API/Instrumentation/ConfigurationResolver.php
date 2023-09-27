<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Instrumentation;

class ConfigurationResolver implements ConfigurationResolverInterface
{
    public function has(string $name): bool
    {
        return $this->getVariable($name) !== null;
    }

    public function getString(string $name): ?string
    {
        return $this->getVariable($name);
    }

    public function getBoolean(string $name): ?bool
    {
        $value = $this->getVariable($name);
        if ($value === null) {
            return null;
        }

        return ($value === 'true');
    }

    public function getInt(string $name): ?int
    {
        $value = $this->getVariable($name);
        if ($value === null) {
            return null;
        }
        if (filter_var($value, FILTER_VALIDATE_INT) === false) {
            //log warning
            return null;
        }

        return (int) $value;
    }

    public function getList(string $name): array
    {
        $value = $this->getVariable($name);
        if ($value === null) {
            return [];
        }

        return explode(',', $value);
    }

    private function getVariable(string $name): ?string
    {
        $value = $_SERVER[$name] ?? null;
        if ($value !== false && !self::isEmpty($value)) {
            assert(is_string($value));

            return $value;
        }
        $value = getenv($name);
        if ($value !== false && !self::isEmpty($value)) {
            return $value;
        }
        $value = ini_get($name);
        if ($value !== false && !self::isEmpty($value)) {
            return $value;
        }

        return null;
    }

    private static function isEmpty($value): bool
    {
        return $value === false || $value === null || $value === '';
    }
}
