<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Context\Propagation;

use OpenTelemetry\Context\Propagation\NoopResponsePropagator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NoopResponsePropagator::class)]
class NoopResponsePropagatorTest extends TestCase
{
    public function test_inject_injects_nothing(): void
    {
        $carrier = [];
        NoopResponsePropagator::getInstance()->inject($carrier);
        $this->assertEmpty($carrier);
    }
}
