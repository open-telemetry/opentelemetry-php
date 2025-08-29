<?php

declare(strict_types=final 1);

namespace OpenTelemetry\Tests\SDK\Trace;

use Nyholm\Psr7\ServerRequest;
use OpenTelemetry\API\Instrumentation\Configurator;
use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\API\Trace\SpanBuilderInterface;
use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\Context\ContextKeys;
use OpenTelemetry\Context\ScopeInterface;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Trace\AutoRootSpan;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(AutoRootSpan::class)]
class AutoRootSpanTest extends TestCase
{
    use TestState;

    /** @var (TracerInterface&MockObject) */
    private TracerInterface $tracer;
    private ScopeInterface $scope;

    #[\Override]
    public function setUp(): void
    {
        $tracerProvider = $this->createMock(TracerProviderInterface::class);
        $this->tracer = $this->createMock(TracerInterface::class);
        $tracerProvider->method('getTracer')->willReturn($this->tracer);

        $this->scope = Configurator::create()
            ->withTracerProvider($tracerProvider)
            ->withPropagator(new TraceContextPropagator())
            ->activate();
    }

    #[\Override]
    public function tearDown(): void
    {
        $this->scope->detach();
    }

    #[BackupGlobals(true)]
    #[DataProvider('enabledProvider')]
    public function test_is_enabled(string $enabled, ?string $method, bool $expected): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_PHP_EXPERIMENTAL_AUTO_ROOT_SPAN, $enabled);
        $_SERVER['REQUEST_METHOD'] = $method;

        $this->assertSame($expected, AutoRootSpan::isEnabled());
    }

    public static function enabledProvider(): array
    {
        return [
            ['true', 'GET', true],
            ['true', null, false],
            ['true', '', false],
            ['false', 'GET', false],
        ];
    }

    #[BackupGlobals(true)]
    public function test_create_request(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/foo';

        $request = AutoRootSpan::createRequest();
        $this->assertNotNull($request);
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/foo', $request->getUri()->getPath());
    }

    public function test_create(): void
    {
        $body = 'hello otel';
        $traceId = 'ff000000000000000000000000000041';
        $spanId = 'ff00000000000041';
        $traceParent = '00-' . $traceId . '-' . $spanId . '-01';
        $request = new ServerRequest('POST', 'https://example.com/foo?bar=baz', ['traceparent' => $traceParent], $body);

        $spanBuilder = $this->createMock(SpanBuilderInterface::class);
        $spanBuilder
            ->expects($this->once())
            ->method('setSpanKind')
            ->with($this->equalTo(SpanKind::KIND_SERVER))
            ->willReturnSelf();
        $spanBuilder
            ->expects($this->once())
            ->method('setStartTimestamp')
            ->willReturnSelf();
        $spanBuilder
            ->expects($this->once())
            ->method('setParent')
            ->with($this->callback(function (ContextInterface $parent) use ($traceId, $spanId) {
                $span = Span::fromContext($parent);
                $this->assertSame($traceId, $span->getContext()->getTraceId());
                $this->assertSame($spanId, $span->getContext()->getSpanId());

                return true;
            }))
            ->willReturnSelf();
        $spanBuilder
            ->expects($this->atLeast(8))
            ->method('setAttribute')
            ->willReturnSelf();

        $this->tracer
            ->expects($this->once())
            ->method('spanBuilder')
            ->with($this->equalTo('POST'))
            ->willReturn($spanBuilder);

        AutoRootSpan::create($request);

        $scope = Context::storage()->scope();
        $this->assertNotNull($scope);
        $scope->detach();
    }

    public function test_shutdown_handler(): void
    {
        $this->setEnvironmentVariable('OTEL_PHP_DEBUG_SCOPES_DISABLED', 'true');
        $span = $this->createMock(SpanInterface::class);
        $span
            ->expects($this->once())
            ->method('end');
        Context::getCurrent()->with(ContextKeys::span(), $span)->activate();

        AutoRootSpan::shutdownHandler();
    }
}
