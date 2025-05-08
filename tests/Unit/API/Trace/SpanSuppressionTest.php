<?php

declare(strict_types=1);

namespace Unit\API\Trace;

use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\SpanSuppression;
use OpenTelemetry\Context\Context;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(SpanSuppression::class)]
class SpanSuppressionTest extends TestCase
{
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
        $this->assertFalse(SpanSuppression::shouldSuppress(SpanKind::KIND_CLIENT));
        $this->assertFalse(SpanSuppression::shouldSuppress(SpanKind::KIND_SERVER));

        $scope1 = SpanSuppression::suppressSpanKind(SpanKind::KIND_CLIENT)->activate();
        $this->assertTrue(SpanSuppression::shouldSuppress(SpanKind::KIND_CLIENT));
        $this->assertFalse(SpanSuppression::shouldSuppress(SpanKind::KIND_SERVER));

        $scope2 = SpanSuppression::suppressSpanKind(SpanKind::KIND_SERVER)->activate();

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

    public function test_store_in_context(): void
    {
        $suppression = SpanSuppression::suppressSpanKind(SpanKind::KIND_SERVER);
        $this->assertFalse(SpanSuppression::shouldSuppress(SpanKind::KIND_SERVER), 'suppression is not active');

        Context::storage()->attach($suppression->storeInContext(Context::getCurrent()));
        $this->assertTrue(SpanSuppression::shouldSuppress(SpanKind::KIND_SERVER), 'suppression is active');

        $scope = Context::storage()->scope();
        $scope?->detach();
        $this->assertFalse(SpanSuppression::shouldSuppress(SpanKind::KIND_SERVER));
    }
}
