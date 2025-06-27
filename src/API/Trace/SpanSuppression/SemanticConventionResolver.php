<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace\SpanSuppression;

/**
 * @experimental
 */
interface SemanticConventionResolver
{
    /**
     * @return list<SemanticConvention>
     */
    public function resolveSemanticConventions(string $name, ?string $version, ?string $schemaUrl): array;
}
