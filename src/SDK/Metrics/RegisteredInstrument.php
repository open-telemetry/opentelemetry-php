<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

/**
 * @internal
 */
final class RegisteredInstrument
{
    public function __construct(
        public bool $dormant,
        public readonly object $configKeepAlive,
    ) {
    }
}
