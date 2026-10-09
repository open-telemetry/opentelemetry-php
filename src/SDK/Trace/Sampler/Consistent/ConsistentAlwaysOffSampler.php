<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Sampler\Consistent;

use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;

/**
 * Consistent sampler that drops every span (returns INVALID_THRESHOLD).
 *
 * @experimental
 */
final class ConsistentAlwaysOffSampler extends ConsistentSampler
{
    private static ?self $instance = null;

    private function __construct()
    {
    }

    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    #[\Override]
    public function getSamplingIntent(
        ContextInterface $parentContext,
        string $spanName,
        int $spanKind,
        AttributesInterface $attributes,
        array $links,
    ): SamplingIntentInterface {
        return new SamplingIntent(
            threshold: ConsistentSamplingUtil::INVALID_THRESHOLD,
            adjustedCountReliable: false,
        );
    }

    #[\Override]
    public function getDescription(): string
    {
        return 'ConsistentAlwaysOffSampler';
    }
}
