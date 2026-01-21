<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace\SpanSuppression;

/**
 * @experimental
 */
final readonly class SemanticConvention
{
    /**
     * @param list<string> $samplingAttributes
     */
    public function __construct(
        public string $name,
        public int $spanKind,
        public array $samplingAttributes,
        public array $attributes,
    ) {
    }
}
