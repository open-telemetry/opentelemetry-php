<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics\View;

use OpenTelemetry\SDK\Metrics\Aggregation\SumAggregation;
use OpenTelemetry\SDK\Metrics\Instrument;
use OpenTelemetry\SDK\Metrics\InstrumentType;
use OpenTelemetry\SDK\Metrics\View\ViewTemplate;
use OpenTelemetry\SDK\Metrics\ViewProjection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ViewTemplate::class)]
final class ViewTemplateTest extends TestCase
{
    public function test_empty_template_returns_instrument_defaults(): void
    {
        $this->assertEquals(
            new ViewProjection(
                'name',
                'unit',
                'description',
                null,
                null,
            ),
            ViewTemplate::create()
                ->project(new Instrument(InstrumentType::COUNTER, 'name', 'unit', 'description')),
        );
    }

    public function test_template_returns_assigned_values(): void
    {
        $this->assertEquals(
            new ViewProjection(
                'v-name',
                'unit',
                'v-description',
                ['foo', 'bar'],
                new SumAggregation(),
            ),
            ViewTemplate::create()
                ->withName('v-name')
                ->withDescription('v-description')
                ->withAttributeKeys(['foo', 'bar'])
                ->withAggregation(new SumAggregation())
                ->project(new Instrument(InstrumentType::COUNTER, 'name', 'unit', 'description')),
        );
    }
}
