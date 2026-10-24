<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Sampler\Consistent;

use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;

/**
 * Drops every span (no threshold, adjusted count not reliable).
 *
 * @experimental
 */
final class ComposableAlwaysOff implements ComposableSamplerInterface
{
    private readonly SamplingIntentInterface $intent;

    public function __construct()
    {
        $this->intent = new SamplingIntent(
            threshold: SamplingIntent::INVALID_THRESHOLD,
            adjustedCountReliable: false,
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
        return 'ComposableAlwaysOff';
    }
}
