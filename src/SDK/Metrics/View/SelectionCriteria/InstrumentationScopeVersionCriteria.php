<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\View\SelectionCriteria;

use OpenTelemetry\SDK\InstrumentationScope;
use OpenTelemetry\SDK\Metrics\Instrument;
use OpenTelemetry\SDK\Metrics\View\SelectionCriteria;

final class InstrumentationScopeVersionCriteria implements SelectionCriteria
{
    private ?string $version;

    public function __construct(?string $version)
    {
        $this->version = $version;
    }

    public function accepts(Instrument $instrument, InstrumentationScope $instrumentationScope): bool
    {
        return $this->version === $instrumentationScope->getVersion();
    }
}
