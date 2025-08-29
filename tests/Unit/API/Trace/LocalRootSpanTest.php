<?php

declare(strict_types=1final );

namespace OpenTelemetry\Tests\Unit\API\Trace;

use OpenTelemetry\API\Trace\LocalRootSpan;
use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\Context\Context;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LocalRootSpan::class)]
class LocalRootSpanTest extends TestCase
{
    public function test_span_with_remote_parent_is_local_root(): void
    {
        $context = Context::getRoot()->with(
            LocalRootSpan::key(),
            Span::wrap(
                SpanContext::createFromRemoteParent(
                    '00000000000000000000000000000001',
                    '0000000000000002',
                )
            )
        );
        $this->assertTrue(LocalRootSpan::isLocalRoot($context));
    }

    public function test_get_local_root_span(): void
    {
        $span = Span::getInvalid();
        $context = LocalRootSpan::store(Context::getCurrent(), $span);
        $scope = $context->activate();

        try {
            $this->assertSame($span, LocalRootSpan::fromContext($context));
            $this->assertSame($span, LocalRootSpan::current());
        } finally {
            $scope->detach();
        }
    }

    public function test_get_local_root_span_when_not_set(): void
    {
        $context = Context::getRoot();
        $scope = $context->activate();

        try {
            $this->assertFalse(LocalRootSpan::fromContext($context)->getContext()->isValid());
            $this->assertFalse(LocalRootSpan::current()->getContext()->isValid());
            $this->assertSame(Span::getInvalid(), LocalRootSpan::current());
        } finally {
            $scope->detach();
        }
    }
}
