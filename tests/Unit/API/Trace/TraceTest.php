<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Trace;

use Exception;
use OpenTelemetry\API\Trace\Noop\NonRecordingSpan;
use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\API\Trace\StatusCode;
use function OpenTelemetry\API\Trace\trace;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;
use WeakReference;

/**
 * @covers \OpenTelemetry\API\Trace\trace
 */
final class TraceTest extends TestCase
{
    public function test_runs_with_provided_span(): void
    {
        $span = new NonRecordingSpan(SpanContext::getInvalid());

        trace($span, fn () => $this->assertSame($span, Span::getCurrent()));
    }

    public function test_restores_previous_span(): void
    {
        $span = new NonRecordingSpan(SpanContext::getInvalid());
        $scope = $span->activate();

        try {
            trace(Span::getInvalid(), fn () => null);

            $this->assertSame($span, Span::getCurrent());
        } finally {
            $scope->detach();
        }
    }

    public function test_returns_closure_result(): void
    {
        $this->assertSame(42, trace(Span::getInvalid(), fn () => 42));
    }

    public function test_provides_args_to_closure(): void
    {
        trace(Span::getInvalid(), fn ($a) => $this->assertSame(42, $a), [42]);
    }

    public function test_ends_span(): void
    {
        $span = $this->createMock(SpanInterface::class);
        $span->expects($this->once())->method('end');

        trace($span, fn () => null);
    }

    public function test_rethrows_exception(): void
    {
        $this->expectException(RuntimeException::class);

        trace(Span::getInvalid(), function (): void {
            throw new RuntimeException();
        });
    }

    public function test_records_exception(): void
    {
        $span = $this->createMock(SpanInterface::class);
        $span->expects($this->once())->method('setStatus')->with(StatusCode::STATUS_ERROR);
        $span->expects($this->once())->method('recordException');

        try {
            trace($span, function (): void {
                throw new RuntimeException();
            });
        } catch (RuntimeException $e) {
        }
    }

    public function test_ends_span_on_exception(): void
    {
        $span = $this->createMock(SpanInterface::class);
        $span->expects($this->once())->method('end');

        try {
            trace($span, function (): void {
                throw new RuntimeException();
            });
        } catch (RuntimeException $e) {
        }
    }

    public function test_exception_does_not_leak_closure_reference(): void
    {
        $c = static function (): void {
            throw new RuntimeException();
        };
        $r = WeakReference::create($c);

        try {
            trace(Span::getInvalid(), $c);
        } catch (Exception $e) {
            $c = null;
            $this->assertNull($r->get());
        }
    }

    public function test_does_not_keep_argument_references(): void
    {
        trace(Span::getInvalid(), function (object $o): void {
            $r = WeakReference::create($o);
            $o = null;

            $this->assertNull($r->get());
        }, [new stdClass()]);
    }
}
