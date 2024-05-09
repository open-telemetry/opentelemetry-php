<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics\Exemplar;

use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Metrics\Data\Exemplar;
use OpenTelemetry\SDK\Metrics\Exemplar\FixedSizeReservoir;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\OpenTelemetry\SDK\Metrics\Exemplar\FixedSizeReservoir::class)]
final class FixedSizeReservoirTest extends TestCase
{
    public function test_reservoir_returns_exemplars(): void
    {
        $reservoir = new FixedSizeReservoir(4);
        $reservoir->offer(0, 5, Attributes::create([]), Context::getRoot(), 7);

        $this->assertEquals([
            0 => [
                new Exemplar(0, 5, 7, Attributes::create([]), null, null),
            ],
        ], Exemplar::groupByIndex($reservoir->collect([0 => Attributes::create([])])));
    }
}
