<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Trace;

use OpenTelemetry\Sdk\Trace\Attributes;
use OpenTelemetry\Sdk\Trace\SamplingResult;
use OpenTelemetry\Trace\Links;
use PHPUnit\Framework\TestCase;

class SpanResultTest extends TestCase
{
    /**
     * @dataProvider provideAttributesAndLinks
     */
    public function testAttributesAndLinksGetters($attributes, $links)
    {
        $result = new SamplingResult(SamplingResult::NOT_RECORD, $attributes, $links);

        $this->assertSame($attributes, $result->getAttributes());
        $this->assertSame($links, $result->getLinks());
    }

    /**
     * Provide different sets of data to test SamplingResult constructor and getters
     */
    public function provideAttributesAndLinks(): array
    {
        return [
            [
                new Attributes(['foo' => 'bar']),
                $this->createMock(Links::class),
            ],
            [
                null,
                null,
            ],
        ];
    }
}
