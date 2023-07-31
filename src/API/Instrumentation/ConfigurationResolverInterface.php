<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Instrumentation;

interface ConfigurationResolverInterface
{
    public function has(string $name): bool;
    public function getString(string $name): ?string;
    public function getBoolean(string $name): ?bool;
    public function getInt(string $name): ?int;
    public function getList(string $name): array;
}
