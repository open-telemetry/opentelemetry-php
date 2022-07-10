<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace;

use OpenTelemetry\API\Trace\TraceStateInterface;
use OpenTelemetry\SDK\Trace\SamplingResult;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\SDK\Trace\SamplingResult
 */
class SamplingResultTest extends TestCase
{
    /**
     * @dataProvider provideAttributesAndLinks
     */
    public function test_attributes_and_links_getters($attributes, $traceState): void
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
                ['foo' => 'bar'],
                $this->createMock(TraceStateInterface::class),
            ],
            [
                [],
                null,
            ],
        ];
    }
}
