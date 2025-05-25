<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanSuppression\SemanticConventionSuppressionStrategy;

use function array_merge;
use OpenTelemetry\API\Instrumentation\SpanSuppression\SemanticConventionResolver;
use OpenTelemetry\SDK\Trace\SpanSuppression\SpanSuppressionStrategy;
use OpenTelemetry\SDK\Trace\SpanSuppression\SpanSuppressor;

/**
 * @experimental
 */
final class SemanticConventionSuppressionStrategy implements SpanSuppressionStrategy
{
    /**
     * @param iterable<SemanticConventionResolver> $resolvers
     */
    public function __construct(
        private readonly iterable $resolvers,
    ) {
    }

    public function getSuppressor(string $name, ?string $version, ?string $schemaUrl): SpanSuppressor
    {
        $semanticConventions = [];
        foreach ($this->resolvers as $resolver) {
            if ($conventions = $resolver->resolveSemanticConventions($name, $version, $schemaUrl)) {
                $semanticConventions[] = $conventions;
            }
        }

        return new SemanticConventionSuppressor(array_merge(...$semanticConventions));
    }
}
