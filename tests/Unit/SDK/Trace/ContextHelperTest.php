<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace;

use OpenTelemetry\SDK\Trace\ContextHelper;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Trace\ContextHelper
 */
class ContextHelperTest extends TestCase
{
    public function test_cleanup(): void
    {
        $tracer = NoopTracer::getInstance();
        $span1 = $tracer->spanBuilder("span1")->startSpan();
        $scope1 = $span1->activate();
        $this->assertNotEquals(Context::getCurrent(), Context::getRoot());
        // Intentionally no detach call

        // Will trigger_error without this
        ContextHelper::cleanup(function() {});
    }
}
