<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Util;

use OpenTelemetry\SDK\Common\Util\DanglingSpansShutdownHandler;
use OpenTelemetry\Context\Context;
use OpenTelemetry\API\Trace\NoopTracer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Common\Util\DanglingSpansShutdownHandler
 */
class DanglingSpansShutdownHelperTest extends TestCase
{
    public function test_cleanup(): void
    {
        $tracer = NoopTracer::getInstance();
        $span1 = $tracer->spanBuilder("span1")->startSpan();
        $scope1 = $span1->activate();
        $this->assertNotEquals(Context::getCurrent(), Context::getRoot());
        // Intentionally no detach call

        // Will trigger_error without this
        DanglingSpansShutdownHandler::shutdown();
    }
}
