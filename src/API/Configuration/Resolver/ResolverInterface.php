<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Configuration\Resolver;

/**
 * @internal
 */
interface ResolverInterface
{
    /**
     * @return mixed
     */
    public function retrieveValue(string $variableName);

    public function hasVariable(string $variableName): bool;
}
