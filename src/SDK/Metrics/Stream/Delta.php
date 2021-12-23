<?php declare(strict_types=1);
namespace OpenTelemetry\SDK\Metrics\Stream;

use GMP;

final class Delta {

    public function __construct(
        public Metric $metric,
        public int|GMP $readers,
        public int $timestamp,
        public ?Delta $prev = null,
    ) {}
}
