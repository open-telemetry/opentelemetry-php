<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Trace;

use ArrayObject;
use OpenTelemetry\API\Trace\NonRecordingSpan;
use OpenTelemetry\SDK\Common\InstrumentationScope\Condition;
use OpenTelemetry\SDK\Common\InstrumentationScope\Predicate;
use OpenTelemetry\SDK\Common\InstrumentationScope\State;
use OpenTelemetry\SDK\Trace\ImmutableSpan;
use OpenTelemetry\SDK\Trace\SpanExporter\InMemoryExporter;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\Tracer;
use OpenTelemetry\SDK\Trace\TracerConfig;
use OpenTelemetry\SDK\Trace\TracerConfigurator;
use OpenTelemetry\SDK\Trace\TracerProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(TracerConfig::class)]
#[CoversClass(TracerConfigurator::class)]
#[CoversClass(Predicate\Attribute::class)]
#[CoversClass(Predicate\AttributeExists::class)]
#[CoversClass(Predicate\Name::class)]
#[CoversClass(Condition::class)]
class TracerConfigTest extends TestCase
{
    public function test_disable_scopes(): void
    {
        $storage = new ArrayObject();
        $exporter = new InMemoryExporter($storage);
        $tracerProvider = TracerProvider::builder()
            ->addSpanProcessor(new SimpleSpanProcessor($exporter))
            ->addTracerConfiguratorCondition(new Predicate\Name('~two~'), State::DISABLED) //disable tracer two
            ->build();
        $tracer_one = $tracerProvider->getTracer('one');
        $tracer_two = $tracerProvider->getTracer('two');
        $tracer_three = $tracerProvider->getTracer('three');

        $parent = $tracer_one->spanBuilder('parent')->startSpan();
        $this->assertTrue($parent->isRecording());
        $parent->setAttribute('a', 1);
        $parentScope = $parent->activate();

        try {
            $child = $tracer_two->spanBuilder('child')->startSpan();
            $child->setAttribute('b', 1);
            $childScope = $child->activate();

            try {
                $this->assertFalse($child->isRecording());
                $grandChild = $tracer_three->spanBuilder('grandchild')->startSpan();
                $this->assertTrue($grandChild->isRecording());
                $grandChild->setAttribute('c', 1);
                $grandChild->end();
            } finally {
                $childScope->detach();
                $child->end();
            }
        } finally {
            $parentScope->detach();
            $parent->end();
        }
        // Only tracerA:parent and tracerC:child should be recorded
        // tracerC:grandchild should list tracerA:parent as its parent
        $this->assertCount(2, $storage, 'only 2 of the 3 spans were recorded');

        // @var ImmutableSpan $gc
        $gc = $storage->offsetGet(0);
        $this->assertSame('grandchild', $gc->getName());

        // @var ImmutableSpan $p
        $p = $storage->offsetGet(1);
        $this->assertSame('parent', $p->getName());

        $this->assertSame($p->getTraceId(), $gc->getTraceId(), 'parent and grandchild are in the same trace');
        $this->assertSame($gc->getParentContext()->getSpanId(), $p->getContext()->getSpanId(), 'parent is the parent of grandchild');
    }

    #[DataProvider('conditionsProvider')]
    public function test_conditions(Predicate $predicate, State $state, bool $expectDisabled): void
    {
        $tracerProvider = TracerProvider::builder()
            ->addTracerConfiguratorCondition($predicate, $state)
            ->build();
        $tracer = $tracerProvider->getTracer(name: 'two', attributes: ['foo' => 'bar']);
        $span = $tracer->spanBuilder('span')->startSpan();
        if ($expectDisabled) {
            $this->assertInstanceOf(NonRecordingSpan::class, $span);
        } else {
            $this->assertNotInstanceOf(NonRecordingSpan::class, $span);
        }
    }

    public static function conditionsProvider(): array
    {
        return [
            'match name + disable' => [new Predicate\Name('~two~'), State::DISABLED, true],
            'match name + enable' => [new Predicate\Name('~two~'), State::ENABLED, false],
            'no match name + disable' => [new Predicate\Name('~one~'), State::DISABLED, false],
            'no match name + enable' => [new Predicate\Name('~one~'), State::ENABLED, false],
            'attribute exists + disable' => [new Predicate\AttributeExists('foo'), State::DISABLED, true],
            'attribute exists + enable' => [new Predicate\AttributeExists('foo'), State::ENABLED, false],
            'attributes matches + disable' => [new Predicate\Attribute('foo', 'bar'), State::DISABLED, true],
            'attribute does not match' => [new Predicate\Attribute('foo', 'no-match'), State::DISABLED, false],
        ];
    }

    public function test_enable_after_disable(): void
    {
        $tracerProvider = TracerProvider::builder()
            ->addTracerConfiguratorCondition(new Predicate\Name('~two~'), State::DISABLED) //disable tracer two
            ->build();
        $tracer = $tracerProvider->getTracer(name: 'two');
        $this->assertInstanceOf(Tracer::class, $tracer);
        $this->assertFalse($tracer->enabled());

        $update = new TracerConfigurator();
        $tracerProvider->updateConfigurator($update);

        $this->assertTrue($tracer->enabled());
    }
}
