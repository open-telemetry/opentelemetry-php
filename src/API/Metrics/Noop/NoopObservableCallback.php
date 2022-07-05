<?php declare(strict_types=1);
namespace OpenTelemetry\API\Metrics\Noop;

use OpenTelemetry\API\Metrics\ObservableCallback;

/**
 * @internal
 */
final class NoopObservableCallback implements ObservableCallback {

    public function detach(): void {
        // no-op
    }
}
