<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Metrics\Noop;

use OpenTelemetry\API\Metrics\ObservableCallbackInterface;

/**
 * @internal
 */
final class NoopObservableCallback implements ObservableCallbackInterface
{
    public function detach(): void
    {
        // no-op
    }
}
