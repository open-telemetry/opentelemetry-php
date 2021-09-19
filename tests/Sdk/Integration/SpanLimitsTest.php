<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Integration\Trace;

use OpenTelemetry\Sdk\Trace\Attributes;
use OpenTelemetry\Sdk\Trace\Link;
use OpenTelemetry\Sdk\Trace\Links;
use OpenTelemetry\Sdk\Trace\SpanContext;
use OpenTelemetry\Sdk\Trace\SpanLimits;
use OpenTelemetry\Sdk\Trace\SpanLimitsBuilder;
use OpenTelemetry\Sdk\Trace\Tracer;
use OpenTelemetry\Sdk\Trace\TracerProvider;
use OpenTelemetry\Trace\SpanKind;
use PHPUnit\Framework\TestCase;

class SpanLimitsTest extends TestCase
{
    public function testSpanAttributeLimits()
    {
        $spanLimits = (new SpanLimitsBuilder())
            ->setAttributeCountLimit(3)
            ->build();

        $span = $this->getTracerSpanWithLimits($spanLimits)->startSpan('test.spanlimits');
        for ($i = 0; $i < 4; $i++) {
            $span->setAttribute('attr' . $i, $i);
        }

        $this->assertCount(3, $span->getAttributes());
        $this->assertEquals(1, $span->getAttributes()->getDroppedAttributesCount());
    }

    public function testSpanEventLimits()
    {
        $spanLimits = (new SpanLimitsBuilder())
            ->setEventCountLimit(3)
            ->setAttributePerEventCountLimit(2)
            ->build();

        $span = $this->getTracerSpanWithLimits($spanLimits)->startSpan('test.spanlimits');
        for ($i = 0; $i < 4; $i++) {
            $span->addEvent('event' . $i, 0, new Attributes(['a1' => 1, 'a2' => 2, 'a3' => 3]));
        }

        $this->assertCount(3, $span->getEvents());
        $this->assertEquals(1, $span->getDroppedEventsCount(), 'Should be dropped exactly one event');

        foreach ($span->getEvents() as $event) {
            $this->assertCount(2, $event->getAttributes());
            $this->assertEquals(1, $event->getAttributes()->getDroppedAttributesCount(), 'Should be dropped exactly one attribute');
        }
    }

    public function testSpanLinksLimits()
    {
        $spanLimits = (new SpanLimitsBuilder())
            ->setLinkCountLimit(3)
            ->setAttributePerLinkCountLimit(2)
            ->build();

        $links = new Links();
        for ($i = 0; $i < 4; $i++) {
            $links->addLink(new Link(SpanContext::getInvalid(), new Attributes(['a1' => 1, 'a2' => 2, 'a3' => 3])));
        }

        $span = $this->getTracerSpanWithLimits($spanLimits)->startSpan('test.spanlimits', null, SpanKind::KIND_INTERNAL, null, $links);

        $this->assertCount(3, $span->getLinks());
        $this->assertEquals(1, $span->getDroppedLinksCount(), 'Should be dropped exactly one link');

        foreach ($span->getLinks() as $link) {
            $this->assertCount(2, $link->getAttributes());
            $this->assertEquals(1, $link->getAttributes()->getDroppedAttributesCount(), 'Should be dropped exactly one attribute');
        }
    }

    private function getTracerSpanWithLimits(SpanLimits $spanLimits): Tracer
    {
        $tracerProvider = new TracerProvider(null, null, null, $spanLimits);

        return $tracerProvider->getTracer('OpenTelemetry.TracerTest');
    }
}
