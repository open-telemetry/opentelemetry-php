<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Instrumentation;

use function serialize;

class KeyGenerator
{
    /**
     * Generate a unique key for an instance of InstrumentationScope.
     */
    public static function generateInstanceKey(InstrumentationScopeInterface $instrumentationScope): string
    {
        return serialize([
            $instrumentationScope->getName(),
            $instrumentationScope->getVersion(),
            $instrumentationScope->getSchemaUrl(),
            $instrumentationScope->getAttributes()->toArray(),
            $instrumentationScope->getAttributes()->getDroppedAttributesCount(),
        ]);
    }
}
