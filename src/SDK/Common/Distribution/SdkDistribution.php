<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Distribution;

use OpenTelemetry\Context\Propagation\ResponsePropagatorInterface;
use OpenTelemetry\SDK\Trace\SpanSuppression\NoopSuppressionStrategy\NoopSuppressionStrategy;
use OpenTelemetry\SDK\Trace\SpanSuppression\SpanSuppressionStrategy;

/**
 * @internal
 */
final class SdkDistribution implements DistributionConfiguration
{
    public function __construct(
        public readonly SpanSuppressionStrategy $spanSuppressionStrategy = new NoopSuppressionStrategy(),
        public readonly bool $internalMetricsEnabled = false,
        public readonly ?ResponsePropagatorInterface $responsePropagator = null,
    ) {
    }
}
