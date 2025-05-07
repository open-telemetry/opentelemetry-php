<?php

declare(strict_types=1);

namespace Unit\API\Trace;

use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\SpanSuppression;
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

    public function test_suppress_client_spans(): void
    {
        $this->assertFalse(SpanSuppression::shouldSuppress(SpanKind::KIND_CLIENT));
        $scope = SpanSuppression::suppressSpanKind([SpanKind::KIND_CLIENT])->activate();

        try {
            $this->assertTrue(SpanSuppression::shouldSuppress(SpanKind::KIND_CLIENT));
            $this->assertFalse(SpanSuppression::shouldSuppress(SpanKind::KIND_SERVER));
        } finally {
            $scope->detach();
        }
        //suppression removed after detach
        $this->assertFalse(SpanSuppression::shouldSuppress(SpanKind::KIND_CLIENT));
    }

    public function test_merge_suppression(): void
    {
        $this->assertFalse(SpanSuppression::shouldSuppress(SpanKind::KIND_CLIENT));
        $scope1 = SpanSuppression::suppressSpanKind([SpanKind::KIND_CLIENT])->activate();
        $scope2 = SpanSuppression::suppressSpanKind([SpanKind::KIND_SERVER])->activate();

        try {
            $this->assertTrue(SpanSuppression::shouldSuppress(SpanKind::KIND_CLIENT));
            $this->assertTrue(SpanSuppression::shouldSuppress(SpanKind::KIND_SERVER));
        } finally {
            $scope1->detach();
            $scope2->detach();
        }
        //suppression removed after detach
        $this->assertFalse(SpanSuppression::shouldSuppress(SpanKind::KIND_CLIENT));
        $this->assertFalse(SpanSuppression::shouldSuppress(SpanKind::KIND_SERVER));
    }
}
