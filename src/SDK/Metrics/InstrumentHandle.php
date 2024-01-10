<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

/**
 * @internal
 */
interface InstrumentHandle
{
    public function getHandle(): Instrument;
}
