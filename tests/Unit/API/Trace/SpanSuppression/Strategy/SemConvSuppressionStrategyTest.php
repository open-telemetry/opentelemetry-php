<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Trace\SpanSuppression\Strategy;

use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\SpanSuppression\Strategy\SemConvSuppressionStrategy;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SemConv\TraceAttributes;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SemConvSuppressionStrategy::class)]
class SemConvSuppressionStrategyTest extends TestCase
{
    public function test_should_suppress(): void
    {
        $suppression = SemConvSuppressionStrategy::suppressSemConv(TraceAttributes::HTTP_REQUEST_METHOD, 'GET');
        $this->assertTrue($suppression->shouldSuppress(SpanKind::KIND_CLIENT, [TraceAttributes::HTTP_REQUEST_METHOD => 'GET']));
        $this->assertFalse($suppression->shouldSuppress(SpanKind::KIND_CLIENT, [TraceAttributes::HTTP_REQUEST_METHOD => 'POST']));
    }

    public function test_store_in_context(): void
    {
        $suppression = SemConvSuppressionStrategy::suppressSemConv(TraceAttributes::HTTP_REQUEST_METHOD, 'PUT');
        $context = $suppression->storeInContext(Context::getRoot());
        $this->assertSame($suppression, $context->get(SemConvSuppressionStrategy::contextKey()));
    }
}
