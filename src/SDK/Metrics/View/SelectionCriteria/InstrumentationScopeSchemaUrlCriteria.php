<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\View\SelectionCriteria;

use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Metrics\Instrument;
use OpenTelemetry\SDK\Metrics\View\SelectionCriteria;

final class InstrumentationScopeSchemaUrlCriteria implements SelectionCriteria
{
    private ?string $schemaUrl;

    public function __construct(?string $schemaUrl)
    {
        $this->schemaUrl = $schemaUrl;
    }

    public function accepts(Instrument $instrument, InstrumentationScopeInterface $instrumentationScope): bool
    {
        return $this->schemaUrl === $instrumentationScope->getSchemaUrl();
    }
}
