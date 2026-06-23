<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Metrics\Noop;

use OpenTelemetry\API\Metrics\Noop\NoopObservableCallback;
use OpenTelemetry\API\Metrics\Noop\NoopObservableGauge;
use OpenTelemetry\API\Metrics\ObservableCallbackInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NoopObservableGauge::class)]
class NoopObservableGaugeTest extends TestCase
{
    private NoopObservableGauge $gauge;

    #[\Override]
    protected function setUp(): void
    {
        $this->gauge = new NoopObservableGauge();
    }

    public function test_observe_returns_noop_observable_callback(): void
    {
        $callback = $this->gauge->observe(fn () => null);
        $this->assertInstanceOf(ObservableCallbackInterface::class, $callback);
        $this->assertInstanceOf(NoopObservableCallback::class, $callback);
    }

    public function test_is_enabled_returns_false(): void
    {
        $this->assertFalse($this->gauge->isEnabled());
    }
}
