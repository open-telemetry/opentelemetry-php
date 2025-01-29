<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

/**
 * Instrument type.
 */
enum InstrumentType
{
    case COUNTER;
    case UP_DOWN_COUNTER;
    case HISTOGRAM;
    /** @experimental */
    case GAUGE;

    case ASYNCHRONOUS_COUNTER;
    case ASYNCHRONOUS_UP_DOWN_COUNTER;
    case ASYNCHRONOUS_GAUGE;
}
