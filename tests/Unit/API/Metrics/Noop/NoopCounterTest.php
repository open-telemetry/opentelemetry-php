<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Metrics\Noop;

use OpenTelemetry\API\Metrics\Noop\NoopCounter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NoopCounter::class)]
class NoopCounterTest extends TestCase
{
    private NoopCounter $counter;

    #[\Override]
    protected function setUp(): void
    {
        $this->counter = new NoopCounter();
    }

    public function test_add_does_not_throw(): void
    {
        $this->counter->add(1);
        $this->counter->add(5, ['key' => 'value']);
        $this->expectNotToPerformAssertions();
    }

    public function test_is_enabled_returns_false(): void
    {
        $this->assertFalse($this->counter->isEnabled());
    }
}
