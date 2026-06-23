<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Metrics\Noop;

use OpenTelemetry\API\Metrics\Noop\NoopHistogram;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NoopHistogram::class)]
class NoopHistogramTest extends TestCase
{
    private NoopHistogram $histogram;

    #[\Override]
    protected function setUp(): void
    {
        $this->histogram = new NoopHistogram();
    }

    public function test_record_does_not_throw(): void
    {
        $this->histogram->record(1);
        $this->histogram->record(3.14, ['key' => 'value']);
        $this->expectNotToPerformAssertions();
    }

    public function test_is_enabled_returns_false(): void
    {
        $this->assertFalse($this->histogram->isEnabled());
    }
}
