<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Trace\SpanSuppression\Strategy;

use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\SpanSuppression\Strategy\SpanKindSuppressionStrategy;
use OpenTelemetry\API\Trace\SpanSuppression\Strategy\StrategyTrait;
use OpenTelemetry\Context\Context;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\TestCase;

#[CoversClass(SpanKindSuppressionStrategy::class)]
#[CoversTrait(StrategyTrait::class)]
class SpanKindSuppressionStrategyTest extends TestCase
{
    public function test_should_suppress(): void
    {
        $suppression = SpanKindSuppressionStrategy::suppressSpanKind(SpanKind::KIND_SERVER);
        $this->assertTrue($suppression->shouldSuppress(SpanKind::KIND_SERVER));
        $this->assertFalse($suppression->shouldSuppress(SpanKind::KIND_INTERNAL));
    }

    public function test_store_in_context(): void
    {
        $suppression = SpanKindSuppressionStrategy::suppressSpanKind(SpanKind::KIND_SERVER);
        $context = $suppression->storeInContext(Context::getRoot());
        $this->assertSame($suppression, $context->get(SpanKindSuppressionStrategy::contextKey()));
    }

    public function test_activate(): void
    {
        $suppression = SpanKindSuppressionStrategy::suppressSpanKind(SpanKind::KIND_SERVER);
        $scope = $suppression->activate();
        $this->assertSame($suppression, Context::getCurrent()->get(SpanKindSuppressionStrategy::contextKey()));
        $scope->detach();
        $this->assertNull(Context::getCurrent()->get(SpanKindSuppressionStrategy::contextKey()));
    }
}
