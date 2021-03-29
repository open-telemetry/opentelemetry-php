<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Trace;

use OpenTelemetry\Sdk\Trace\Attributes;
use OpenTelemetry\Sdk\Trace\SamplingResult;
use OpenTelemetry\Trace\TraceState;
use PHPUnit\Framework\TestCase;

class SamplingResultTest extends TestCase
{
    /**
     * @dataProvider provideAttributesAndLinks
     */
    public function testAttributesAndLinksGetters($attributes, $traceState)
    {
        $result = new SamplingResult(SamplingResult::DROP, $attributes, $traceState);

        $this->assertSame($attributes, $result->getAttributes());
        $this->assertSame($traceState, $result->getTraceState());
    }

    /**
     * Provide different sets of data to test SamplingResult constructor and getters
     */
    public function provideAttributesAndLinks(): array
    {
        return [
            [
                new Attributes(['foo' => 'bar']),
                $this->createMock(TraceState::class),
            ],
            [
                null,
                null,
            ],
        ];
    }
}
