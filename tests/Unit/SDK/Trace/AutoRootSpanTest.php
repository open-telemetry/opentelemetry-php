<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace;

use Nyholm\Psr7\ServerRequest;
use OpenTelemetry\API\Common\Time\ClockInterface;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\HookManagerInterface;
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
        (new \ReflectionProperty(AutoRootSpan::class, 'rootScope'))->setValue(null, null);
        (new \ReflectionProperty(AutoRootSpan::class, 'hookManager'))->setValue(null, null);
        (new \ReflectionProperty(AutoRootSpan::class, 'responseHeaderHookRegistered'))->setValue(null, false);
        (new \ReflectionProperty(AutoRootSpan::class, 'capturedResponseHeaders'))->setValue(null, []);
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
            ->with($this->equalTo('POST /foo'))
            ->willReturn($spanBuilder);

        AutoRootSpan::create($request);

        $scope = Context::storage()->scope();
        $this->assertNotNull($scope);
        $scope->detach();
    }

    public function test_create_converts_request_time_float_to_nanoseconds(): void
    {
        $requestTimeFloat = 1700000000.5;
        $expectedNanos = (int) ($requestTimeFloat * (float) ClockInterface::NANOS_PER_SECOND);

        $request = new ServerRequest(
            'GET',
            'https://example.com/',
            [],
            null,
            '1.1',
            ['REQUEST_TIME_FLOAT' => $requestTimeFloat],
        );

        $span = $this->createStub(SpanInterface::class);
        $spanBuilder = $this->createMock(SpanBuilderInterface::class);
        $spanBuilder->method('setSpanKind')->willReturnSelf();
        $spanBuilder->method('setParent')->willReturnSelf();
        $spanBuilder->method('setAttribute')->willReturnSelf();
        $spanBuilder->method('startSpan')->willReturn($span);
        $spanBuilder
            ->expects($this->once())
            ->method('setStartTimestamp')
            ->with($this->identicalTo($expectedNanos))
            ->willReturnSelf();

        $this->tracer->method('spanBuilder')->willReturn($spanBuilder);

        AutoRootSpan::create($request);

        Context::storage()->scope()?->detach();
    }

    public function test_create_start_timestamp_preserves_sub_second_precision_without_request_time_float(): void
    {
        $request = new ServerRequest('GET', 'https://example.com/');

        $capturedTimestamp = null;
        $span = $this->createStub(SpanInterface::class);
        $spanBuilder = $this->createMock(SpanBuilderInterface::class);
        $spanBuilder->method('setSpanKind')->willReturnSelf();
        $spanBuilder->method('setParent')->willReturnSelf();
        $spanBuilder->method('setAttribute')->willReturnSelf();
        $spanBuilder->method('startSpan')->willReturn($span);
        $spanBuilder
            ->expects($this->once())
            ->method('setStartTimestamp')
            ->with($this->callback(function (int $timestamp) use (&$capturedTimestamp): bool {
                $capturedTimestamp = $timestamp;

                return true;
            }))
            ->willReturnSelf();

        $this->tracer->method('spanBuilder')->willReturn($spanBuilder);

        $before = (int) (microtime(true) * (float) ClockInterface::NANOS_PER_SECOND);
        AutoRootSpan::create($request);
        $after = (int) (microtime(true) * (float) ClockInterface::NANOS_PER_SECOND);

        $this->assertNotNull($capturedTimestamp);
        $this->assertGreaterThanOrEqual($before, $capturedTimestamp);
        $this->assertLessThanOrEqual($after, $capturedTimestamp);

        Context::storage()->scope()?->detach();
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

    public function test_create_uses_url_group_in_span_name(): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_PHP_TRANSACTION_URL_GROUPS, '/users/*,/orders/*');
        $request = new ServerRequest('GET', 'https://example.com/users/42');

        $span = $this->createMock(SpanInterface::class);
        $spanBuilder = $this->createMock(SpanBuilderInterface::class);
        $spanBuilder->method('setSpanKind')->willReturnSelf();
        $spanBuilder->method('setStartTimestamp')->willReturnSelf();
        $spanBuilder->method('setParent')->willReturnSelf();
        $spanBuilder->method('setAttribute')->willReturnSelf();
        $spanBuilder->method('startSpan')->willReturn($span);
        $this->tracer
            ->expects($this->once())
            ->method('spanBuilder')
            ->with($this->equalTo('GET /users/*'))
            ->willReturn($spanBuilder);

        AutoRootSpan::create($request);

        Context::storage()->scope()?->detach();
    }

    public function test_create_captures_configured_request_headers(): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_INSTRUMENTATION_HTTP_SERVER_CAPTURE_REQUEST_HEADERS, 'X-Foo,X-Missing');
        $request = new ServerRequest('GET', 'https://example.com/', ['X-Foo' => ['a', 'b'], 'X-Other' => 'c']);

        $span = $this->createMock(SpanInterface::class);
        $span
            ->expects($this->once())
            ->method('setAttribute')
            ->with('http.request.header.x-foo', ['a', 'b']);
        $spanBuilder = $this->createMock(SpanBuilderInterface::class);
        $spanBuilder->method('setSpanKind')->willReturnSelf();
        $spanBuilder->method('setStartTimestamp')->willReturnSelf();
        $spanBuilder->method('setParent')->willReturnSelf();
        $spanBuilder->method('setAttribute')->willReturnSelf();
        $spanBuilder->method('startSpan')->willReturn($span);
        $this->tracer->method('spanBuilder')->willReturn($spanBuilder);

        AutoRootSpan::create($request);

        Context::storage()->scope()?->detach();
    }

    public function test_shutdown_handler_sets_captured_response_headers(): void
    {
        $this->setEnvironmentVariable('OTEL_PHP_DEBUG_SCOPES_DISABLED', 'true');
        (new \ReflectionProperty(AutoRootSpan::class, 'capturedResponseHeaders'))->setValue(null, ['x-bar' => ['1', '2']]);
        $span = $this->createMock(SpanInterface::class);
        $span
            ->expects($this->once())
            ->method('setAttribute')
            ->with('http.response.header.x-bar', ['1', '2']);
        $span->expects($this->once())->method('end');
        Context::getCurrent()->with(ContextKeys::span(), $span)->activate();

        try {
            AutoRootSpan::shutdownHandler();
        } finally {
            (new \ReflectionProperty(AutoRootSpan::class, 'capturedResponseHeaders'))->setValue(null, []);
        }
    }

    #[BackupGlobals(true)]
    #[DataProvider('cliSpanNameProvider')]
    public function test_create_cli_span_name_and_no_http_attributes(?string $scriptName, string $groups, string $expected): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_PHP_TRANSACTION_URL_GROUPS, $groups);
        unset($_SERVER['SCRIPT_NAME']);
        if ($scriptName !== null) {
            $_SERVER['SCRIPT_NAME'] = $scriptName;
        }

        $span = $this->createMock(SpanInterface::class);
        $span->expects($this->never())->method('setAttribute');
        $spanBuilder = $this->createMock(SpanBuilderInterface::class);
        $spanBuilder->method('setSpanKind')->willReturnSelf();
        $spanBuilder->method('setStartTimestamp')->willReturnSelf();
        $spanBuilder->method('setParent')->willReturnSelf();
        $spanBuilder->expects($this->never())->method('setAttribute');
        $spanBuilder->method('startSpan')->willReturn($span);
        $this->tracer
            ->expects($this->once())
            ->method('spanBuilder')
            ->with($this->equalTo($expected))
            ->willReturn($spanBuilder);

        AutoRootSpan::create(new ServerRequest('GET', 'http://localhost/'), true);

        Context::storage()->scope()?->detach();
    }

    public static function cliSpanNameProvider(): array
    {
        return [
            'script name' => ['bin/console', '', 'bin/console'],
            'grouped' => ['bin/console', 'bin/*', 'bin/*'],
            'no script name' => [null, '', '<script>'],
            'empty script name' => ['', '', '<script>'],
        ];
    }

    #[DataProvider('disabledProvider')]
    public function test_start_does_nothing_when_disabled(?string $value): void
    {
        if ($value !== null) {
            $this->setEnvironmentVariable(Variables::OTEL_PHP_TRANSACTION_SPAN_ENABLED_CLI, $value);
        }
        $this->tracer->expects($this->never())->method('spanBuilder');

        AutoRootSpan::start();
    }

    public static function disabledProvider(): array
    {
        // the unit tests run in the CLI SAPI, so only the CLI flag is decisive here
        return ['default is off' => [null], 'explicitly off' => ['false']];
    }

    public function test_start_creates_root_span_in_cli(): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_PHP_TRANSACTION_SPAN_ENABLED_CLI, 'true');
        $span = $this->createMock(SpanInterface::class);
        $spanBuilder = $this->createMock(SpanBuilderInterface::class);
        $spanBuilder->method('setSpanKind')->willReturnSelf();
        $spanBuilder->method('setStartTimestamp')->willReturnSelf();
        $spanBuilder->method('setParent')->willReturnSelf();
        $spanBuilder->method('startSpan')->willReturn($span);
        $this->tracer->expects($this->once())->method('spanBuilder')->willReturn($spanBuilder);

        AutoRootSpan::start();

        $scope = Context::storage()->scope();
        $this->assertNotNull($scope);
        $scope->detach();
    }

    #[BackupGlobals(true)]
    public function test_start_does_nothing_for_excluded_url(): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_PHP_TRANSACTION_SPAN_ENABLED_CLI, 'true');
        $this->setEnvironmentVariable(Variables::OTEL_PHP_EXCLUDED_URLS, '/health,/metrics');
        $_SERVER['REQUEST_URI'] = '/health';
        $this->tracer->expects($this->never())->method('spanBuilder');

        AutoRootSpan::start();
    }

    #[BackupGlobals(true)]
    public function test_start_creates_span_for_url_not_excluded(): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_PHP_TRANSACTION_SPAN_ENABLED_CLI, 'true');
        $this->setEnvironmentVariable(Variables::OTEL_PHP_EXCLUDED_URLS, '/health');
        $_SERVER['REQUEST_URI'] = '/orders';
        $span = $this->createMock(SpanInterface::class);
        $spanBuilder = $this->createMock(SpanBuilderInterface::class);
        $spanBuilder->method('setSpanKind')->willReturnSelf();
        $spanBuilder->method('setStartTimestamp')->willReturnSelf();
        $spanBuilder->method('setParent')->willReturnSelf();
        $spanBuilder->method('startSpan')->willReturn($span);
        $this->tracer->expects($this->once())->method('spanBuilder')->willReturn($spanBuilder);

        AutoRootSpan::start();

        Context::storage()->scope()?->detach();
    }

    #[BackupGlobals(true)]
    public function test_is_enabled_by_transaction_span_flag_and_experimental_alias(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->assertFalse(AutoRootSpan::isEnabled());

        $this->setEnvironmentVariable(Variables::OTEL_PHP_TRANSACTION_SPAN_ENABLED, 'true');
        $this->assertTrue(AutoRootSpan::isEnabled());
    }

    public function test_start_does_nothing_when_root_span_already_started(): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_PHP_TRANSACTION_SPAN_ENABLED_CLI, 'true');
        $span = $this->createMock(SpanInterface::class);
        $spanBuilder = $this->createMock(SpanBuilderInterface::class);
        $spanBuilder->method('setSpanKind')->willReturnSelf();
        $spanBuilder->method('setStartTimestamp')->willReturnSelf();
        $spanBuilder->method('setParent')->willReturnSelf();
        $spanBuilder->method('startSpan')->willReturn($span);
        $this->tracer->expects($this->once())->method('spanBuilder')->willReturn($spanBuilder);

        AutoRootSpan::start();
        AutoRootSpan::start();

        Context::storage()->scope()?->detach();
    }

    #[BackupGlobals(true)]
    #[DataProvider('conflictingFlagsProvider')]
    public function test_transaction_span_flag_wins_over_experimental_alias(?string $transaction, ?string $experimental, bool $expected): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        if ($transaction !== null) {
            $this->setEnvironmentVariable(Variables::OTEL_PHP_TRANSACTION_SPAN_ENABLED, $transaction);
        }
        if ($experimental !== null) {
            $this->setEnvironmentVariable(Variables::OTEL_PHP_EXPERIMENTAL_AUTO_ROOT_SPAN, $experimental);
        }

        $this->assertSame($expected, AutoRootSpan::isEnabled());
    }

    public static function conflictingFlagsProvider(): array
    {
        return [
            'both unset' => [null, null, false],
            'only alias true' => [null, 'true', true],
            'only alias false' => [null, 'false', false],
            'new true, alias false' => ['true', 'false', true],
            'new false, alias true' => ['false', 'true', false],
            'both true' => ['true', 'true', true],
        ];
    }

    public function test_response_header_hook_is_registered_on_the_hook_manager(): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_INSTRUMENTATION_HTTP_SERVER_CAPTURE_RESPONSE_HEADERS, 'X-Foo');
        $hookManager = $this->useFakeHookManager();
        $this->tracer->method('spanBuilder')->willReturn($this->spanBuilderReturning($this->createMock(SpanInterface::class)));

        AutoRootSpan::create(new ServerRequest('GET', 'https://example.com/'));
        // the hook is registered only once, even when the root span is created again
        AutoRootSpan::create(new ServerRequest('GET', 'https://example.com/'));

        $this->assertCount(1, $hookManager->hooks);
        $this->assertNull($hookManager->hooks[0]['class']);
        $this->assertSame('header', $hookManager->hooks[0]['function']);
        $this->assertNotNull($hookManager->hooks[0]['pre']);
        $this->assertNull($hookManager->hooks[0]['post']);

        Context::storage()->scope()?->detach();
        Context::storage()->scope()?->detach();
    }

    public function test_response_header_hook_is_not_registered_without_configured_headers(): void
    {
        $hookManager = $this->useFakeHookManager();
        $this->tracer->method('spanBuilder')->willReturn($this->spanBuilderReturning($this->createMock(SpanInterface::class)));

        AutoRootSpan::create(new ServerRequest('GET', 'https://example.com/'));

        $this->assertSame([], $hookManager->hooks);

        Context::storage()->scope()?->detach();
    }

    public function test_response_header_hook_records_configured_headers_only(): void
    {
        $this->setEnvironmentVariable('OTEL_PHP_DEBUG_SCOPES_DISABLED', 'true');
        $this->setEnvironmentVariable(Variables::OTEL_INSTRUMENTATION_HTTP_SERVER_CAPTURE_RESPONSE_HEADERS, 'X-Foo,X-Multi');
        $hookManager = $this->useFakeHookManager();
        $recorded = [];
        $span = $this->createMock(SpanInterface::class);
        $span->method('setAttribute')->willReturnCallback(
            static function (string $key, mixed $value) use (&$recorded, $span): SpanInterface {
                $recorded[$key] = $value;

                return $span;
            },
        );
        $this->tracer->method('spanBuilder')->willReturn($this->spanBuilderReturning($span));

        AutoRootSpan::create(new ServerRequest('GET', 'https://example.com/'));
        $pre = $hookManager->hooks[0]['pre'];
        $this->assertInstanceOf(\Closure::class, $pre);
        $pre(null, ['X-Foo: bar'], null, 'header', null, null);
        $pre(null, ['X-Foo: replaced'], null, 'header', null, null);
        $pre(null, ['X-Multi: a'], null, 'header', null, null);
        $pre(null, ['X-Multi:  b ', false], null, 'header', null, null);
        $pre(null, ['X-Other: ignored'], null, 'header', null, null);
        $pre(null, ['no colon'], null, 'header', null, null);
        $pre(null, [], null, 'header', null, null);
        $pre(null, [42], null, 'header', null, null);
        AutoRootSpan::shutdownHandler();

        $this->assertSame(
            ['http.response.header.x-foo' => ['replaced'], 'http.response.header.x-multi' => ['a', 'b']],
            $recorded,
        );
    }

    #[BackupGlobals(true)]
    public function test_start_treats_an_emulated_web_request_in_the_cli_as_a_web_request(): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_PHP_TRANSACTION_SPAN_ENABLED, 'true');
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/foo?bar=baz';

        $span = $this->createMock(SpanInterface::class);
        $this->tracer
            ->expects($this->once())
            ->method('spanBuilder')
            ->with($this->equalTo('GET /foo'))
            ->willReturn($this->spanBuilderReturning($span));

        AutoRootSpan::start();

        Context::storage()->scope()?->detach();
    }

    #[BackupGlobals(true)]
    public function test_start_does_not_create_a_web_span_for_an_emulated_request_when_web_spans_are_disabled(): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_PHP_TRANSACTION_SPAN_ENABLED_CLI, 'true');
        $this->setEnvironmentVariable(Variables::OTEL_PHP_TRANSACTION_SPAN_ENABLED, 'false');
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->tracer->expects($this->never())->method('spanBuilder');

        AutoRootSpan::start();
    }

    /**
     * @param SpanInterface&MockObject $span
     */
    private function spanBuilderReturning(SpanInterface $span): SpanBuilderInterface
    {
        $span->method('storeInContext')->willReturnCallback(
            static fn (ContextInterface $context): ContextInterface => $context->with(ContextKeys::span(), $span),
        );
        $spanBuilder = $this->createMock(SpanBuilderInterface::class);
        $spanBuilder->method('setSpanKind')->willReturnSelf();
        $spanBuilder->method('setStartTimestamp')->willReturnSelf();
        $spanBuilder->method('setParent')->willReturnSelf();
        $spanBuilder->method('setAttribute')->willReturnSelf();
        $spanBuilder->method('startSpan')->willReturn($span);

        return $spanBuilder;
    }

    /**
     * @return object{hooks: list<array{class: ?string, function: string, pre: ?\Closure, post: ?\Closure}>} a hook manager that records the hooks registered on it
     */
    private function useFakeHookManager(): object
    {
        $manager = new class() implements HookManagerInterface {
            /** @var list<array{class: ?string, function: string, pre: ?\Closure, post: ?\Closure}> */
            public array $hooks = [];

            #[\Override]
            public function hook(?string $class, string $function, ?\Closure $preHook = null, ?\Closure $postHook = null): void
            {
                $this->hooks[] = ['class' => $class, 'function' => $function, 'pre' => $preHook, 'post' => $postHook];
            }
        };
        (new \ReflectionProperty(AutoRootSpan::class, 'hookManager'))->setValue(null, $manager);

        return $manager;
    }
}
