<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\Resolver;

interface ResolverInterface
{
    /**
     * @return mixed
     */
    public function retrieveValue(string $variableName);

    public function hasVariable(string $variableName): bool;
}
