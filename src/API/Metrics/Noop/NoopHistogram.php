<?php declare(strict_types=1);
namespace OpenTelemetry\API\Metrics\Noop;

use OpenTelemetry\Context\Context;
use OpenTelemetry\API\Metrics\Histogram;

/**
 * @internal
 */
final class NoopHistogram implements Histogram {

    public function record(float|int $amount, iterable $attributes = [], Context|false|null $context = null): void {
        // no-op
    }
}
