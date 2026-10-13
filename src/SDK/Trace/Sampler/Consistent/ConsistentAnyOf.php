<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Sampler\Consistent;

use function implode;
use InvalidArgumentException;
use OpenTelemetry\API\Trace\TraceStateInterface;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use function sprintf;

/**
 * Consistent sampler that samples a span when ANY of its delegate samplers would sample it.
 *
 * Composition rules (per spec):
 * - The combined threshold is the minimum of all valid delegate thresholds (lowest threshold =
 *   most permissive = highest probability of sampling).
 * - Adjusted count is reliable if any delegate whose threshold equals the resulting minimum
 *   also reports reliable=true.
 * - Attributes and TraceState updates from all delegates are merged.
 *
 * @experimental
 */
final class ConsistentAnyOf extends ConsistentSampler
{
    /** @var list<ComposableInterface> */
    private readonly array $delegates;
    private readonly string $description;

    /**
     * @param ComposableInterface ...$delegates At least one delegate is required
     * @throws InvalidArgumentException if no delegates are provided
     */
    public function __construct(ComposableInterface ...$delegates)
    {
        if ($delegates === []) {
            throw new InvalidArgumentException('ConsistentAnyOf requires at least one delegate sampler.');
        }

        $this->delegates = array_values($delegates);
        $this->description = sprintf(
            'ConsistentAnyOf{%s}',
            implode(',', array_map(static fn (ComposableInterface $d) => $d->getDescription(), $delegates)),
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
        /** @var list<SamplingIntentInterface> $intents */
        $intents = [];

        $minimumThreshold = ConsistentSamplingUtil::INVALID_THRESHOLD;
        $isAdjustedCountReliable = false;

        foreach ($this->delegates as $delegate) {
            $intent = $delegate->getSamplingIntent($parentContext, $spanName, $spanKind, $attributes, $links);
            $intents[] = $intent;

            $delegateThreshold = $intent->getThreshold();

            if (!ConsistentSamplingUtil::isValidThreshold($delegateThreshold)) {
                continue;
            }

            if (!ConsistentSamplingUtil::isValidThreshold($minimumThreshold)) {
                // First valid threshold
                $minimumThreshold = $delegateThreshold;
                $isAdjustedCountReliable = $intent->isAdjustedCountReliable();
            } elseif ($delegateThreshold === $minimumThreshold) {
                // Same threshold — combine reliability
                if ($intent->isAdjustedCountReliable()) {
                    $isAdjustedCountReliable = true;
                }
            } elseif ($delegateThreshold < $minimumThreshold) {
                // Lower threshold = more permissive; take this one
                $minimumThreshold = $delegateThreshold;
                $isAdjustedCountReliable = $intent->isAdjustedCountReliable();
            }
        }

        return new class($minimumThreshold, $isAdjustedCountReliable, $intents) implements SamplingIntentInterface {
            /**
             * @param list<SamplingIntentInterface> $intents
             */
            public function __construct(
                private readonly int $threshold,
                private readonly bool $reliable,
                private readonly array $intents,
            ) {
            }

            #[\Override]
            public function getThreshold(): int
            {
                return $this->threshold;
            }

            #[\Override]
            public function isAdjustedCountReliable(): bool
            {
                return $this->reliable;
            }

            #[\Override]
            public function getAttributes(): iterable
            {
                $merged = [];
                foreach ($this->intents as $intent) {
                    foreach ($intent->getAttributes() as $key => $value) {
                        $merged[$key] = $value;
                    }
                }

                return $merged;
            }

            #[\Override]
            public function updateTraceState(TraceStateInterface $parentState, bool $sampled): TraceStateInterface
            {
                foreach ($this->intents as $intent) {
                    $parentState = $intent->updateTraceState($parentState, $sampled);
                }

                return $parentState;
            }
        };
    }

    #[\Override]
    public function getDescription(): string
    {
        return $this->description;
    }
}
