<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Sampler\Consistent;

use InvalidArgumentException;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use function sprintf;

/**
 * Consistent sampler that uses a fixed rejection threshold derived from a sampling probability.
 *
 * When the threshold equals {@see ConsistentSamplingUtil::MAX_THRESHOLD} (i.e. probability = 0),
 * the sampler behaves like {@see ConsistentAlwaysOffSampler}.
 *
 * @experimental
 */
final class ConsistentFixedThresholdSampler extends ConsistentSampler
{
    private readonly SamplingIntentInterface $intent;
    private readonly string $description;

    /**
     * @param int $threshold Rejection threshold in [MIN_THRESHOLD, MAX_THRESHOLD]
     * @throws InvalidArgumentException if threshold is out of range
     */
    public function __construct(int $threshold)
    {
        ConsistentSamplingUtil::checkThreshold($threshold);

        if ($threshold === ConsistentSamplingUtil::MAX_THRESHOLD) {
            // Zero probability → behave like AlwaysOff (no reliable threshold)
            $thresholdStr = 'max';
            $this->intent = new SamplingIntent(
                threshold: ConsistentSamplingUtil::INVALID_THRESHOLD,
                adjustedCountReliable: false,
            );
        } else {
            $thresholdStr = ConsistentSamplingUtil::encodeThreshold($threshold);
            $this->intent = new SamplingIntent(
                threshold: $threshold,
                adjustedCountReliable: true,
            );
        }

        $this->description = sprintf(
            'ConsistentFixedThresholdSampler{threshold=%s, sampling probability=%g}',
            $thresholdStr,
            ConsistentSamplingUtil::calculateSamplingProbability($threshold),
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
        return $this->intent;
    }

    #[\Override]
    public function getDescription(): string
    {
        return $this->description;
    }
}
