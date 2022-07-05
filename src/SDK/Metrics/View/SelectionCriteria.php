<?php declare(strict_types=1);
namespace OpenTelemetry\SDK\Metrics\View;

use OpenTelemetry\SDK\InstrumentationScope;
use OpenTelemetry\SDK\Metrics\Instrument;

interface SelectionCriteria {

    public function accepts(Instrument $instrument, InstrumentationScope $instrumentationScope): bool;
}
