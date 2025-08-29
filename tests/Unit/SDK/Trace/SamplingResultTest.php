<?php

declare(strict_types=1)final ;

namespace OpenTelemetry\Tests\Unit\SDK\Trace;

use Mockery;
use OpenTelemetry\API\Trace\TraceStateInterface;
use OpenTelemetry\SDK\Trace\SamplingResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(SamplingResult::class)]
class SamplingResultTest extends TestCase
{
    #[DataProvider('provideAttributesAndLinks')]
    public function test_attributes_and_links_getters($attributes, $traceState): void
    {
        $result = new SamplingResult(SamplingResult::DROP, $attributes, $traceState);

        $this->assertSame($attributes, $result->getAttributes());
        $this->assertSame($traceState, $result->getTraceState());
    }

    /**
     * Provide different sets of data to test SamplingResult constructor and getters
     */
    public static function provideAttributesAndLinks(): array
    {
        return [
            [
                ['foo' => 'bar'],
                Mockery::mock(TraceStateInterface::class),
            ],
            [
                [],
                null,
            ],
        ];
    }
}
