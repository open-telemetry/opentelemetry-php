<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace\SpanSuppression;

/**
 * @experimental
 */
final class SemanticConvention
{
    /**
     * @param list<string> $samplingAttributes
     */
    public function __construct(
        public readonly string $name,
        public readonly int $spanKind,
        public readonly array $samplingAttributes,
        public readonly array $attributes,
    ) {
    }
}
