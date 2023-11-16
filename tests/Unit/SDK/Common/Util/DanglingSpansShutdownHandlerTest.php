<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Util;

use OpenTelemetry\API\Trace\NoopTracer;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Util\DanglingSpansShutdownHandler;
use PHPUnit\Framework\TestCase;

/**
 * Intentionally no detach call
 *
 * Normally clients would:
 * $scope = $span->activate();
 * try {
 *    ... do something ...
 * } finally {
 *   $scope->detach();
 * }
 *
 * But we're simulating an early exit() call or something like that
 * that doesn't allow the detach to happen.
 *
 * @covers \OpenTelemetry\SDK\Common\Util\DanglingSpansShutdownHandler
 */
class DanglingSpansShutdownHandlerTest extends TestCase
{
    public function test_shutdown(): void
    {
        $tracer = NoopTracer::getInstance();
        $span1 = $tracer->spanBuilder('span1')->startSpan();
        $scope1 = $span1->activate();
        $this->assertNotEquals(Context::getCurrent(), Context::getRoot());

        // Will trigger_error without this
        DanglingSpansShutdownHandler::shutdown();
        $this->assertEquals(Context::getCurrent(), Context::getRoot());
    }
}
