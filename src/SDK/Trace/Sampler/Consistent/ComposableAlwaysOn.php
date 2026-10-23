<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Sampler\Consistent;

use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;

/**
 * Samples every span (threshold 0).
 *
 * @experimental
 */
final class ComposableAlwaysOn implements ComposableSamplerInterface
{
    private readonly SamplingIntentInterface $intent;

    public function __construct()
    {
        $this->intent = new SamplingIntent(
            threshold: ConsistentSamplingUtil::MIN_THRESHOLD,
            adjustedCountReliable: true,
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
        return 'ComposableAlwaysOn';
    }
}
