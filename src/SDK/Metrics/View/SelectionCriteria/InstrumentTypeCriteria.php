<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\View\SelectionCriteria;

use function in_array;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Metrics\Instrument;
use OpenTelemetry\SDK\Metrics\InstrumentType;
use OpenTelemetry\SDK\Metrics\View\SelectionCriteriaInterface;

final class InstrumentTypeCriteria implements SelectionCriteriaInterface
{
    private readonly array $instrumentTypes;

    /**
     * @param InstrumentType|InstrumentType[] $instrumentType
     */
    public function __construct(array|InstrumentType $instrumentType)
    {
        $this->instrumentTypes = is_array($instrumentType) ? $instrumentType : [$instrumentType];
    }

    public function accepts(Instrument $instrument, InstrumentationScopeInterface $instrumentationScope): bool
    {
        return in_array($instrument->type, $this->instrumentTypes, true);
    }
}
