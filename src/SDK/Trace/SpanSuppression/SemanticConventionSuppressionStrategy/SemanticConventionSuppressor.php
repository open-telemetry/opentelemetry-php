<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanSuppression\SemanticConventionSuppressionStrategy;

use function array_key_exists;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\SDK\Trace\SpanSuppression\NoopSuppressionStrategy\NoopSuppression;
use OpenTelemetry\SDK\Trace\SpanSuppression\SpanSuppression;
use OpenTelemetry\SDK\Trace\SpanSuppression\SpanSuppressor;

/**
 * @internal
 */
final class SemanticConventionSuppressor implements SpanSuppressor
{
    /**
     * @param array<int, list<CompiledSemanticConvention>> $semanticConventions
     * @param array<int, list<CompiledSemanticConventionAttribute>> $attributeMap
     */
    public function __construct(
        private readonly array $semanticConventions,
        private readonly array $attributeMap,
    ) {
    }

    #[\Override]
    public function resolveSuppression(int $spanKind, array $attributes): SpanSuppression
    {
        $excluded = 0;
        foreach ($this->attributeMap[$spanKind] ?? [] as $attribute) {
            // If attribute is present: exclude all semconvs not containing this attribute
            // If attribute is not present: exclude all semconvs containing this attribute as sampling relevant attribute
            $excluded |= array_key_exists($attribute->name, $attributes)
                ? $attribute->notIncludedIn
                : $attribute->samplingRelevantIn;
        }

        $semanticConventions = [];
        foreach ($this->semanticConventions[$spanKind] ?? [] as $i => $semanticConvention) {
            if ($excluded >> $i & 1) {
                continue;
            }
            foreach ($attributes as $attribute => $_) {
                if (!$semanticConvention->attributes->matches($attribute)) {
                    continue 2;
                }
            }

            $semanticConventions[] = $semanticConvention->name;
        }

        if (!$semanticConventions && $spanKind === SpanKind::KIND_INTERNAL) {
            return new NoopSuppression();
        }

        return new SemanticConventionSuppression(
            contextKey: match ($spanKind) {
                SpanKind::KIND_INTERNAL => SemanticConventionSuppressionContextKey::Internal,
                SpanKind::KIND_CLIENT => SemanticConventionSuppressionContextKey::Client,
                SpanKind::KIND_SERVER => SemanticConventionSuppressionContextKey::Server,
                SpanKind::KIND_PRODUCER => SemanticConventionSuppressionContextKey::Producer,
                SpanKind::KIND_CONSUMER => SemanticConventionSuppressionContextKey::Consumer,
            },
            semanticConventions: $semanticConventions,
        );
    }
}
