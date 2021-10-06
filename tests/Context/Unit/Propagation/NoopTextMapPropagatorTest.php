<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Context\Unit\Propagation;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\Propagation\NoopTextMapPropagator;
use PHPUnit\Framework\TestCase;

class NoopTextMapPropagatorTest extends TestCase
{
    public function test_fields(): void
    {
        $this->assertEmpty(NoopTextMapPropagator::getInstance()->fields());
    }

    public function test_extract_contextIsUnchanged(): void
    {
        $this->assertSame(
            Context::getRoot(),
            NoopTextMapPropagator::getInstance()->extract([])
        );
    }

    public function test_inject_injectsNothing(): void
    {
        $carrier = [];
        NoopTextMapPropagator::getInstance()->inject($carrier);
        $this->assertEmpty($carrier);
    }
}
