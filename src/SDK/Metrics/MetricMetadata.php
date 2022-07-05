<?php declare(strict_types=1);
namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\SDK\Metrics\Data\Temporality;

interface MetricMetadata {

    public function instrumentType(): InstrumentType;

    public function name(): string;

    public function unit(): ?string;

    public function description(): ?string;

    public function temporality(): Temporality;
}
