<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanSuppression\SemanticConventionSuppressionStrategy;

/**
 * @internal
 */
final readonly class CompiledSemanticConventionAttribute
{
    public function __construct(
        public string $name,
        public int $notSamplingRelevantIn,
        public int $includedIn,
    ) {
    }
}
