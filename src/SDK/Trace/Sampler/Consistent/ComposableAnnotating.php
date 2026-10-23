<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Sampler\Consistent;

use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use function sprintf;

/**
 * Delegates the decision to another composable sampler and adds attributes to sampled spans.
 * Attributes of the delegate are kept; on a key conflict the attributes of this sampler win.
 *
 * @experimental
 */
final class ComposableAnnotating implements ComposableSamplerInterface
{
    private readonly string $description;

    /**
     * @param array<non-empty-string, mixed> $attributes Attributes to add to sampled spans
     */
    public function __construct(
        private readonly array $attributes,
        private readonly ComposableSamplerInterface $delegate,
    ) {
        $this->description = sprintf(
            'ComposableAnnotating{%s,%s}',
            $delegate->getDescription(),
            (string) json_encode($attributes, JSON_PARTIAL_OUTPUT_ON_ERROR),
        );
    }

    #[\Override]
    public function getSamplingIntent(
        ContextInterface $parentContext,
        string $spanName,
        int $spanKind,
        AttributesInterface $attributes,
        array $links,
    ): SamplingIntentInterface {
        $intent = $this->delegate->getSamplingIntent($parentContext, $spanName, $spanKind, $attributes, $links);

        // The spread operator would renumber integer keys, which attribute names like "123" become
        $delegateAttributes = $intent->getAttributes();
        $merged = array_replace(
            is_array($delegateAttributes) ? $delegateAttributes : iterator_to_array($delegateAttributes),
            $this->attributes,
        );

        return new SamplingIntent(
            threshold: $intent->getThreshold(),
            adjustedCountReliable: $intent->isAdjustedCountReliable(),
            attributes: $merged,
            traceStateUpdater: $intent->updateTraceState(...),
        );
    }

    #[\Override]
    public function getDescription(): string
    {
        return $this->description;
    }
}
