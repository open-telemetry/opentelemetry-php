<?php declare(strict_types=1);
namespace OpenTelemetry\API\Metrics;

interface Observer {

    public function observe(float|int $amount, iterable $attributes = []): void;
}
