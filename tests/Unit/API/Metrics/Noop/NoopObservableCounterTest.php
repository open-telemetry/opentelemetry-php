<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Metrics\Noop;

use OpenTelemetry\API\Metrics\Noop\NoopObservableCallback;
use OpenTelemetry\API\Metrics\Noop\NoopObservableCounter;
use OpenTelemetry\API\Metrics\ObservableCallbackInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NoopObservableCounter::class)]
class NoopObservableCounterTest extends TestCase
{
    private NoopObservableCounter $counter;

    #[\Override]
    protected function setUp(): void
    {
        $this->counter = new NoopObservableCounter();
    }

    public function test_observe_returns_noop_observable_callback(): void
    {
        $callback = $this->counter->observe(fn () => null);
        $this->assertInstanceOf(ObservableCallbackInterface::class, $callback);
        $this->assertInstanceOf(NoopObservableCallback::class, $callback);
    }

    public function test_is_enabled_returns_false(): void
    {
        $this->assertFalse($this->counter->isEnabled());
    }
}
