<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Metrics\Noop;

use OpenTelemetry\API\Metrics\Noop\NoopObservableCallback;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NoopObservableCallback::class)]
class NoopObservableCallbackTest extends TestCase
{
    public function test_detach_does_not_throw(): void
    {
        $callback = new NoopObservableCallback();
        $callback->detach();
        $this->expectNotToPerformAssertions();
    }
}
