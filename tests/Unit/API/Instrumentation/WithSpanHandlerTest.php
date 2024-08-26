<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Instrumentation;

use ArrayObject;
use OpenTelemetry\API\Instrumentation\Configurator;
use OpenTelemetry\API\Instrumentation\WithSpanHandler;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\Context\ScopeInterface;
use OpenTelemetry\SDK\Trace\ImmutableSpan;
use OpenTelemetry\SDK\Trace\SpanExporter\InMemoryExporter;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(WithSpanHandler::class)]
class WithSpanHandlerTest extends TestCase
{
    private ScopeInterface $scope;
    private ArrayObject $storage;

    public function setUp(): void
    {
        $this->storage = new ArrayObject();
        $tracerProvider = new TracerProvider(
            new SimpleSpanProcessor(
                new InMemoryExporter($this->storage)
            )
        );

        $this->scope = Configurator::create()
            ->withTracerProvider($tracerProvider)
            ->activate();
    }

    public function tearDown(): void
    {
        $this->scope->detach();
    }

    public function test_creates_span_with_all_values(): void
    {
        $name = 'foo';
        $kind = SpanKind::KIND_CLIENT;
        $attributes = ['foo' => 'bar'];
        WithSpanHandler::pre(
            null,
            [],
            'My\\Class',
            'some_function',
            'a_file.php',
            99,
            ['name' => $name, 'span_kind' => $kind],
            $attributes,
        );
        $this->assertCount(0, $this->storage);
        WithSpanHandler::post(null, [], null, null);

        $this->assertCount(1, $this->storage);
        /** @var ImmutableSpan $span */
        $span = $this->storage->offsetGet(0);

        $this->assertSame($name, $span->getName());
        $this->assertSame($kind, $span->getKind());
        $this->assertSame([
            'code.function' => 'some_function',
            'code.namespace' => 'My\Class',
            'code.filepath' => 'a_file.php',
            'code.lineno' => 99,
            'foo' => 'bar',
        ], $span->getAttributes()->toArray());
    }

    #[DataProvider('defaultsProvider')]
    public function test_defaults(string $class, string $function, string $expected): void
    {
        $this->assertCount(0, $this->storage);
        WithSpanHandler::pre(null, [], $class, $function, null, null, [], []);
        WithSpanHandler::post(null, [], null, null);

        $this->assertCount(1, $this->storage);
        /** @var ImmutableSpan $span */
        $span = $this->storage->offsetGet(0);

        $this->assertSame($expected, $span->getName());
        $this->assertSame(SpanKind::KIND_INTERNAL, $span->getKind());
    }

    public static function defaultsProvider(): array
    {
        return [
            ['My\\Class', 'foo', 'My\\Class::foo'],
            ['', 'foo', 'foo'],
        ];
    }
}
