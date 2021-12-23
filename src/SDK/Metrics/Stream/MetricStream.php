<?php declare(strict_types=1);
namespace OpenTelemetry\SDK\Metrics\Stream;

use OpenTelemetry\SDK\Metrics\Data\Data;
use OpenTelemetry\SDK\Metrics\Data\Temporality;

interface MetricStream {

    public function register(Temporality $temporality): int;

    public function unregister(int $reader): void;

    public function collect(int $reader, int $timestamp): Data;
}
