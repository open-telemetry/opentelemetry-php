<?php declare(strict_types=1);
namespace OpenTelemetry\API\Metrics\Noop;

use OpenTelemetry\Context\Context;
use OpenTelemetry\API\Metrics\UpDownCounter;

/**
 * @internal
 */
final class NoopUpDownCounter implements UpDownCounter {

    public function add(float|int $amount, iterable $attributes = [], Context|false|null $context = null): void {
        // no-op
    }
}
