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
     * @param array<int, list<string>> $semanticConventions
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
        $candidates = $filter = (1 << count($this->semanticConventions[$spanKind] ?? [])) - 1;
        foreach ($this->attributeMap[$spanKind] ?? [] as $attribute) {
            // If attribute is present: exclude all semconvs not containing this attribute
            // If attribute is not present: exclude all semconvs containing this attribute as sampling relevant attribute
            if (array_key_exists($attribute->name, $attributes)) {
                $filter &= $attribute->includedIn;
            } else {
                $candidates &= $attribute->notSamplingRelevantIn;
            }
        }

        if (!$candidates && $spanKind === SpanKind::KIND_INTERNAL) {
            static $suppression = new NoopSuppression();

            return $suppression;
        }

        if ($candidates & $filter) {
            $candidates &= $filter;
        }

        $semanticConventions = [];
        foreach ($this->semanticConventions[$spanKind] ?? [] as $i => $semanticConvention) {
            if ($candidates >> $i & 1) {
                $semanticConventions[] = $semanticConvention;
            }
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
