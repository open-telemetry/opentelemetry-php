<?php declare(strict_types=1);
namespace OpenTelemetry\SDK\Metrics\Stream;

final class LastRead {

    public function __construct(
        public Metric $metric,
        public int $timestamp,
    ) {}
}
