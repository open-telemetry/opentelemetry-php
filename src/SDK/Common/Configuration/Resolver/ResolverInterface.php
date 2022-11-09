<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Configuration\Resolver;

interface ResolverInterface
{
    public function retrieveValue(string $variableName): ?string;

    public function hasVariable(string $variableName): bool;
}
