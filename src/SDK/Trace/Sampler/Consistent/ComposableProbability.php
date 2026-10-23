<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Sampler\Consistent;

use InvalidArgumentException;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use function sprintf;

/**
 * Samples spans with a fixed probability.
 *
 * A ratio of 0 (or one so small that it rounds to a rejection threshold of 2^56) behaves like
 * {@see ComposableAlwaysOff}, as the specification recommends.
 *
 * @experimental
 */
final class ComposableProbability implements ComposableSamplerInterface
{
    private readonly SamplingIntentInterface $intent;
    private readonly string $description;

    /**
     * @param float $ratio Sampling probability in [0, 1]
     * @throws InvalidArgumentException if the ratio is out of range
     */
    public function __construct(float $ratio)
    {
        ConsistentSamplingUtil::checkProbability($ratio);

        $threshold = ConsistentSamplingUtil::calculateThreshold($ratio);
        $this->intent = $threshold === ConsistentSamplingUtil::MAX_THRESHOLD
            ? new SamplingIntent(threshold: SamplingIntent::INVALID_THRESHOLD, adjustedCountReliable: false)
            : new SamplingIntent(threshold: $threshold, adjustedCountReliable: true);
        $this->description = sprintf('ComposableProbability{ratio=%g}', $ratio);
    }

    #[\Override]
    public function getSamplingIntent(
        ContextInterface $parentContext,
        string $spanName,
        int $spanKind,
        AttributesInterface $attributes,
        array $links,
    ): SamplingIntentInterface {
        return $this->intent;
    }

    #[\Override]
    public function getDescription(): string
    {
        return $this->description;
    }
}
