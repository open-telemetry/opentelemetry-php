<?php

declare(strict_types=1);

namespace Unit\API\Trace;

use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\SpanSuppression;
use OpenTelemetry\API\Trace\SpanSuppression\Strategy\SpanKindSuppressionStrategy;
use OpenTelemetry\Context\Context;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(SpanSuppression::class)]
class SpanSuppressionTest extends TestCase
{
    public function tearDown(): void
    {
        SpanSuppression::setStrategies([SpanSuppression::NOOP]);
    }

    #[DataProvider('spanKindProvider')]
    public function test_should_not_suppress_by_default(int $kind): void
    {
        $this->assertFalse(SpanSuppression::shouldSuppress($kind));
    }

    public static function spanKindProvider(): array
    {
        return [
            [SpanKind::KIND_SERVER],
            [SpanKind::KIND_CLIENT],
            [SpanKind::KIND_CONSUMER],
            [SpanKind::KIND_PRODUCER],
            [SpanKind::KIND_INTERNAL],
        ];
    }

    public function test_suppression(): void
    {
        SpanSuppression::setStrategies([SpanSuppression::SPAN_KIND]);

        $this->assertFalse(SpanSuppression::shouldSuppress(SpanKind::KIND_CLIENT));
        $this->assertFalse(SpanSuppression::shouldSuppress(SpanKind::KIND_SERVER));

        $scope1 = SpanKindSuppressionStrategy::suppressSpanKind(SpanKind::KIND_CLIENT)->activate();
        $this->assertTrue(SpanSuppression::shouldSuppress(SpanKind::KIND_CLIENT));
        $this->assertFalse(SpanSuppression::shouldSuppress(SpanKind::KIND_SERVER));

        $scope2 = SpanKindSuppressionStrategy::suppressSpanKind(SpanKind::KIND_SERVER)->activate();

        try {
            $this->assertTrue(SpanSuppression::shouldSuppress(SpanKind::KIND_CLIENT));
            $this->assertTrue(SpanSuppression::shouldSuppress(SpanKind::KIND_SERVER));
        } finally {
            $scope2->detach();
            $scope1->detach();
        }

        //suppression removed after detach
        $this->assertFalse(SpanSuppression::shouldSuppress(SpanKind::KIND_CLIENT));
        $this->assertFalse(SpanSuppression::shouldSuppress(SpanKind::KIND_SERVER));
    }

    public function test_suppression_stored_in_context(): void
    {
        SpanSuppression::setStrategies([SpanSuppression::SPAN_KIND]);

        $this->assertFalse(SpanSuppression::shouldSuppress(SpanKind::KIND_CLIENT));
        $this->assertFalse(SpanSuppression::shouldSuppress(SpanKind::KIND_SERVER));

        $suppression = SpanKindSuppressionStrategy::suppressSpanKind(SpanKind::KIND_CLIENT);

        $context = $suppression->storeInContext(Context::getCurrent());
        Context::storage()->attach($context);

        try {
            $this->assertTrue(SpanSuppression::shouldSuppress(SpanKind::KIND_CLIENT));
            $this->assertFalse(SpanSuppression::shouldSuppress(SpanKind::KIND_SERVER));
        } finally {
            $scope = Context::storage()->scope();
            $scope?->detach();
        }

        $this->assertFalse(SpanSuppression::shouldSuppress(SpanKind::KIND_CLIENT));
        $this->assertFalse(SpanSuppression::shouldSuppress(SpanKind::KIND_SERVER));
    }
}
