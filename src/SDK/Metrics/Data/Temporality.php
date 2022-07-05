<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Data;

enum Temporality {

    case Delta;
    case Cumulative;
}
