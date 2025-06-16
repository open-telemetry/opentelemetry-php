<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Configuration\ConfigEnv;

/**
 * Helper class to access environment-based configuration.
 */
interface EnvResolver
{
    /**
     * Resolves a string-valued environment variable.
     *
     * @param string $name environment variable name
     * @return string|null value of the environment variable, or null if not set or invalid
     *
     * @see https://opentelemetry.io/docs/specs/otel/configuration/sdk-environment-variables/#string
     */
    public function string(string $name): ?string;

    /**
     * Resolves an enum-valued environment variable.
     *
     * @param string $name environment variable name
     * @param list<string> $values list of permissible enum values
     * @return string|null value of the environment variable, of null if not set or invalid
     *
     * @see https://opentelemetry.io/docs/specs/otel/configuration/sdk-environment-variables/#enum
     */
    public function enum(string $name, array $values): ?string;

    /**
     * Resolves a boolean-valued environment variable.
     *
     * Allowed values:
     * - case-insensitive "true"
     * - case-insensitive "false"
     *
     * @param string $name environment variable name
     * @return bool|null value of the environment variable, or null if not set or invalid
     *
     * @see https://opentelemetry.io/docs/specs/otel/configuration/sdk-environment-variables/#boolean-value
     */
    public function bool(string $name): ?bool;

    /**
     * Resolves an integer-valued environment variable.
     *
     * @param string $name environment variable name
     * @param int|null $min lower limit (inclusive), defaults to 0
     * @param int|null $max upper limit (inclusive), defaults to 2^31-1
     * @return int|null value of the environment variable, or null if not set or invalid
     *
     * @see https://opentelemetry.io/docs/specs/otel/configuration/sdk-environment-variables/#numeric-value
     */
    public function int(string $name, ?int $min = 0, ?int $max = ~(-1 << 31)): int|null;

    /**
     * Resolves a numeric-valued environment variable.
     *
     * @param string $name environment variable name
     * @param int|float|null $min lower limit (inclusive), defaults to 0
     * @param int|float|null $max upper limit (inclusive), defaults to 2^31-1
     * @return int|float|null value of the environment variable, or null if not set or invalid
     *
     * @see https://opentelemetry.io/docs/specs/otel/configuration/sdk-environment-variables/#numeric-value
     */
    public function numeric(string $name, int|float|null $min = 0, int|float|null $max = ~(-1 << 31)): float|int|null;

    /**
     * Resolves a list-valued environment variable.
     *
     * @param string $name environment variable name
     * @return list<string>|null value of the environment variable, or null if not set or invalid
     */
    public function list(string $name): ?array;

    /**
     * Resolves a map-valued environment variable.
     *
     * @param string $name environment variable name
     * @return array<string, string>|null value of the environment variable, or null if not set or invalid
     */
    public function map(string $name): ?array;
}
