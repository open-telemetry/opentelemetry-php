<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Metrics\Noop;

use OpenTelemetry\API\Metrics\Noop\NoopGauge;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NoopGauge::class)]
class NoopGaugeTest extends TestCase
{
    private NoopGauge $gauge;

    #[\Override]
    protected function setUp(): void
    {
        $this->gauge = new NoopGauge();
    }

    public function test_record_does_not_throw(): void
    {
        $this->gauge->record(1);
        $this->gauge->record(3.14, ['key' => 'value']);
        $this->expectNotToPerformAssertions();
    }

    public function test_is_enabled_returns_false(): void
    {
        $this->assertFalse($this->gauge->isEnabled());
    }
}
