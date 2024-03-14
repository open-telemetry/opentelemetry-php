<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\View\SelectionCriteria;

use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Metrics\Instrument;
use OpenTelemetry\SDK\Metrics\View\SelectionCriteriaInterface;

final class InstrumentationScopeVersionCriteria implements SelectionCriteriaInterface
{
    public function __construct(private readonly ?string $version)
    {
    }

    public function accepts(Instrument $instrument, InstrumentationScopeInterface $instrumentationScope): bool
    {
        return $this->version === $instrumentationScope->getVersion();
    }
}
