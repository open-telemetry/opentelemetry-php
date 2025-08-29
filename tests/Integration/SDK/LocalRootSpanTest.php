<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Integration\SDK;

use OpenTelemetry\API\Trace\LocalRootSpan;
use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Trace\TracerProvider;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
class LocalRootSpanTest extends TestCase
{
    private SpanInterface $span;

    #[\Override]
    public function setUp(): void
    {
        $tracerProvider = new TracerProvider();
        $this->span = $tracerProvider->getTracer('test')->spanBuilder('my-local-root-span')->startSpan();
    }

    public function test_active_root_span_is_local_root(): void
    {
        $scope = $this->span->activate();

        try {
            $this->assertSame($this->span, LocalRootSpan::current());
        } finally {
            $scope->detach();
        }
        $this->assertSame(Span::getInvalid(), LocalRootSpan::current(), 'root span ended, a local root span does not exist');
    }

    public function test_root_span_stored_in_context_is_local_root(): void
    {
        $root = Context::getRoot();
        Context::storage()->attach($this->span->storeInContext($root));
        $this->assertSame($this->span, LocalRootSpan::current());
        $scope = Context::storage()->scope();
        $this->assertNotNull($scope);
        $this->assertSame($this->span, Span::fromContext($scope->context()));
        $scope->detach();
        $this->assertSame(Span::getInvalid(), LocalRootSpan::current(), 'root span ended, a local root span does not exist');
    }
}
