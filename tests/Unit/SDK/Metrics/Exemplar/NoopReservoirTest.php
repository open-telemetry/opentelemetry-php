<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics\Exemplar;

use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Metrics\Exemplar\NoopReservoir;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\OpenTelemetry\SDK\Metrics\Exemplar\NoopReservoir::class)]
final class NoopReservoirTest extends TestCase
{
    public function test_reservoir_does_not_return_exemplars(): void
    {
        $reservoir = new NoopReservoir();
        $reservoir->offer(0, 5, Attributes::create([]), Context::getRoot(), 7);

        $this->assertCount(0, $reservoir->collect([0 => Attributes::create([])]));
    }
}
