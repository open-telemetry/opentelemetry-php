<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Integration\SDK\Trace;

use ArrayObject;
use OpenTelemetry\SDK\Common\InstrumentationScope\Configurator;
use OpenTelemetry\SDK\Trace\SpanExporter\InMemoryExporter;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\Tracer;
use OpenTelemetry\SDK\Trace\TracerConfig;
use OpenTelemetry\SDK\Trace\TracerProvider;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
class TracerConfigTest extends TestCase
{
    /**
     * If a Tracer is disabled, it MUST behave equivalently to No-op Tracer
     */
    #[Group('trace-compliance')]
    public function test_disable_scopes(): void
    {
        $storage = new ArrayObject();
        $exporter = new InMemoryExporter($storage);
        $tracerProvider = TracerProvider::builder()
            ->addSpanProcessor(new SimpleSpanProcessor($exporter))
            ->setConfigurator(
                Configurator::tracer()
                ->with(static fn (TracerConfig $config) => $config->setDisabled(false), name: '*')
                ->with(static fn (TracerConfig $config) => $config->setDisabled(true), name: 'B')
            )
            ->build();
        $tracerA = $tracerProvider->getTracer('A');
        $tracerB = $tracerProvider->getTracer('B');
        $tracerC = $tracerProvider->getTracer('C');

        $parent = $tracerA->spanBuilder('parent')->startSpan();
        $this->assertTrue($parent->isRecording());
        $parent->setAttribute('a', 1);
        $parentScope = $parent->activate();

        try {
            $child = $tracerB->spanBuilder('child')->startSpan();
            $child->setAttribute('b', 1);
            $childScope = $child->activate();

            try {
                $this->assertFalse($child->isRecording());
                $grandChild = $tracerC->spanBuilder('grandchild')->startSpan();
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

    public function test_disable_scope_then_enable(): void
    {
        $storage = new ArrayObject();
        $exporter = new InMemoryExporter($storage);
        $tracerProvider = TracerProvider::builder()
            ->addSpanProcessor(new SimpleSpanProcessor($exporter))
            ->setConfigurator(
                Configurator::tracer()
                ->with(static fn (TracerConfig $config) => $config->setDisabled(true), name: 'B')
            )
            ->build();
        $tracerA = $tracerProvider->getTracer('A');
        $tracerB = $tracerProvider->getTracer('B');
        $tracerC = $tracerProvider->getTracer('C');

        $parent = $tracerA->spanBuilder('parent')->startSpan();
        $this->assertTrue($parent->isRecording());
        $parent->setAttribute('a', 1);
        $parentScope = $parent->activate();

        try {
            $child = $tracerB->spanBuilder('child')->startSpan();
            $child->setAttribute('b', 1);
            $childScope = $child->activate();
            $tracerProvider->updateConfigurator(Configurator::tracer()); //re-enable tracer two
            $sibling = $tracerB->spanBuilder('sibling')->startSpan();
            $siblingScope = $sibling->activate();

            try {
                $this->assertFalse($child->isRecording());
                $grandChild = $tracerC->spanBuilder('grandchild')->startSpan();
                $this->assertTrue($grandChild->isRecording());
                $grandChild->setAttribute('c', 1);
                $grandChild->end();
            } finally {
                $siblingScope->detach();
                $sibling->end();
                $childScope->detach();
                $child->end();
            }
        } finally {
            $parentScope->detach();
            $parent->end();
        }
        // tracerA:parent, tracerB:sibling and tracerC:grandchild should be recorded
        // tracerC:grandchild should list tracerB:sibling as its parent
        $this->assertCount(3, $storage, 'only 3 of the 4 spans were recorded');

        // @var ImmutableSpan $gc
        $gc = $storage->offsetGet(0);
        $this->assertSame('grandchild', $gc->getName());

        // @var ImmutableSpan $s
        $s = $storage->offsetGet(1);
        $this->assertSame('sibling', $s->getName());

        // @var ImmutableSpan $p
        $p = $storage->offsetGet(2);
        $this->assertSame('parent', $p->getName());

        $this->assertSame($p->getTraceId(), $gc->getTraceId(), 'parent and grandchild are in the same trace');
        $this->assertSame($p->getTraceId(), $s->getTraceId(), 'parent and sibling are in the same trace');
        $this->assertSame($gc->getParentContext()->getSpanId(), $s->getContext()->getSpanId(), 'sibling is the parent of grandchild');
        $this->assertSame($s->getParentContext()->getSpanId(), $p->getContext()->getSpanId(), 'parent is the parent of sibling');
    }

    public function test_reenable_enables_tracer(): void
    {
        $tracerProvider = TracerProvider::builder()
            ->setConfigurator(
                Configurator::tracer()
                ->with(static fn (TracerConfig $config) => $config->setDisabled(true), name: 'two')
            )
            ->build();
        $tracer = $tracerProvider->getTracer(name: 'two');
        $this->assertInstanceOf(Tracer::class, $tracer);
        $this->assertFalse($tracer->isEnabled());

        $tracerProvider->updateConfigurator(Configurator::tracer()); //reset

        $this->assertTrue($tracer->isEnabled());
    }
}
