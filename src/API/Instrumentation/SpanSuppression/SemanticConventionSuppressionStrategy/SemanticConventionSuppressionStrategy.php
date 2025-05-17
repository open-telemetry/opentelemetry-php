<?php declare(strict_types=1);
namespace OpenTelemetry\API\Instrumentation\SpanSuppression\SemanticConventionSuppressionStrategy;

use OpenTelemetry\API\Instrumentation\SpanSuppression\SpanSuppressionStrategy;
use OpenTelemetry\API\Instrumentation\SpanSuppression\SpanSuppressor;
use OpenTelemetry\API\Instrumentation\SpanSuppression\SemanticConventionResolver;
use function array_merge;

final class SemanticConventionSuppressionStrategy implements SpanSuppressionStrategy {

    /**
     * @param iterable<SemanticConventionResolver> $resolvers
     */
    public function __construct(
        private readonly iterable $resolvers,
    ) {}

    public function getSuppressor(string $name, ?string $version, ?string $schemaUrl): SpanSuppressor {
        $semanticConventions = [];
        foreach ($this->resolvers as $resolver) {
            if ($conventions = $resolver->resolveSemanticConventions($name, $version, $schemaUrl)) {
                $semanticConventions[] = $conventions;
            }
        }

        return new SemanticConventionSuppressor(array_merge(...$semanticConventions));
    }
}
