<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics\Exemplar;

use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Metrics\Data\Exemplar;
use OpenTelemetry\SDK\Metrics\Exemplar\HistogramBucketReservoir;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\OpenTelemetry\SDK\Metrics\Exemplar\HistogramBucketReservoir::class)]
final class HistogramBucketReservoirTest extends TestCase
{
    public function test_reservoir_returns_exemplars(): void
    {
        $reservoir = new HistogramBucketReservoir([0]);
        $reservoir->offer(0, 5, Attributes::create([]), Context::getRoot(), 7);
        $reservoir->offer(0, -5, Attributes::create([]), Context::getRoot(), 8);
        $reservoir->offer(0, 7, Attributes::create([]), Context::getRoot(), 9);

        $this->assertEquals([
            0 => [
                new Exemplar(0, -5, 8, Attributes::create([]), null, null),
                new Exemplar(0, 7, 9, Attributes::create([]), null, null),
            ],
        ], Exemplar::groupByIndex($reservoir->collect([0 => Attributes::create([])])));
    }
}
