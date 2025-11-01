<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanSuppression\SemanticConventionSuppressionStrategy;

use function array_key_last;
use function array_merge;
use function assert;
use OpenTelemetry\API\Trace\SpanSuppression\SemanticConventionResolver;
use OpenTelemetry\SDK\Trace\SpanSuppression\SpanSuppressionStrategy;
use OpenTelemetry\SDK\Trace\SpanSuppression\SpanSuppressor;
use function strcspn;
use function strlen;

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

    #[\Override]
    public function getSuppressor(string $name, ?string $version, ?string $schemaUrl): SpanSuppressor
    {
        $semanticConventions = [];
        foreach ($this->resolvers as $resolver) {
            $semanticConventions[] = $resolver->resolveSemanticConventions($name, $version, $schemaUrl);
        }
        $semanticConventions = array_merge(...$semanticConventions);

        $lookup = [];
        foreach ($semanticConventions as $semanticConvention) {
            foreach ($semanticConvention->samplingAttributes as $attribute) {
                assert(strcspn($attribute, '*?') === strlen($attribute));
                $lookup[$semanticConvention->spanKind][$attribute] ??= [0, 0];
            }
        }

        $compiledSemanticConventions = [];
        foreach ($semanticConventions as $semanticConvention) {
            $attributes = new WildcardPattern();
            foreach ($semanticConvention->samplingAttributes as $attribute) {
                $attributes->add($attribute);
            }
            foreach ($semanticConvention->attributes as $attribute) {
                $attributes->add($attribute);
            }

            $compiledSemanticConventions[$semanticConvention->spanKind][] = $semanticConvention->name;
            $i = array_key_last($compiledSemanticConventions[$semanticConvention->spanKind]);

            foreach ($semanticConvention->samplingAttributes as $attribute) {
                $lookup[$semanticConvention->spanKind][$attribute][0] |= 1 << $i;
            }
            foreach (array_keys($lookup[$semanticConvention->spanKind]) as $attribute) {
                if (!$attributes->matches($attribute)) {
                    $lookup[$semanticConvention->spanKind][$attribute][1] |= 1 << $i;
                }
            }
        }

        $compiledLookupAttributes = [];
        foreach ($lookup as $spanKind => $attributes) {
            foreach ($attributes as $attribute => $masks) {
                $compiledLookupAttributes[$spanKind][] = new CompiledSemanticConventionAttribute($attribute, ~$masks[0], ~$masks[1]);
            }
        }

        return new SemanticConventionSuppressor($compiledSemanticConventions, $compiledLookupAttributes);
    }
}
