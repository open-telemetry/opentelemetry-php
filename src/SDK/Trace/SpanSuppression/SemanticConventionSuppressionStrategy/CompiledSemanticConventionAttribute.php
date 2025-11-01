<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanSuppression\SemanticConventionSuppressionStrategy;

/**
 * @internal
 */
final class CompiledSemanticConventionAttribute
{
    public function __construct(
        public readonly string $name,
        public readonly int $notSamplingRelevantIn,
        public readonly int $includedIn,
    ) {
    }
}
