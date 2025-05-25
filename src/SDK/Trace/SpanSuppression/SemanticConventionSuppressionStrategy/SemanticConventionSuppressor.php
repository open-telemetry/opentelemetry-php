<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanSuppression\SemanticConventionSuppressionStrategy;

use function array_key_exists;
use function count;
use OpenTelemetry\API\Instrumentation\SpanSuppression\SemanticConvention;
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
     * @param iterable<SemanticConvention> $semanticConventions
     */
    public function __construct(
        private readonly iterable $semanticConventions,
    ) {
    }

    public function resolveSuppression(int $spanKind, array $attributes): SpanSuppression
    {
        $semanticConventions = [];
        foreach ($this->semanticConventions as $entry) {
            if ($entry->spanKind !== $spanKind) {
                continue;
            }
            foreach ($entry->samplingAttributes as $attribute) {
                if (!array_key_exists($attribute, $attributes)) {
                    continue 2;
                }
            }
            $matchingAttributes = 0;
            foreach ($entry->attributes as $attribute) {
                if (array_key_exists($attribute, $attributes)) {
                    $matchingAttributes++;
                }
            }
            if ($matchingAttributes !== count($attributes) - count($entry->samplingAttributes)) {
                continue;
            }

            $semanticConventions[] = $entry->name;
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
