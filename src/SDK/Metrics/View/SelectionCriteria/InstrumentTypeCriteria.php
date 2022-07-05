<?php declare(strict_types=1);
namespace OpenTelemetry\SDK\Metrics\View\SelectionCriteria;

use OpenTelemetry\SDK\InstrumentationScope;
use OpenTelemetry\SDK\Metrics\Instrument;
use OpenTelemetry\SDK\Metrics\InstrumentType;
use OpenTelemetry\SDK\Metrics\View\SelectionCriteria;
use function in_array;

final class InstrumentTypeCriteria implements SelectionCriteria {

    private array $instrumentTypes;

    /**
     * @param InstrumentType|InstrumentType[] $instrumentType
     */
    public function __construct(InstrumentType|array $instrumentType) {
        $this->instrumentTypes = (array) $instrumentType;
    }

    public function accepts(Instrument $instrument, InstrumentationScope $instrumentationScope): bool {
        return in_array($instrument->type, $this->instrumentTypes, true);
    }
}
