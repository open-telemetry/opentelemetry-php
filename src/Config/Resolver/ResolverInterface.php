<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\Resolver;

interface ResolverInterface
{
    public function retrieveValue(string $variableName): mixed;

    public function hasVariable(string $variableName): bool;
}
