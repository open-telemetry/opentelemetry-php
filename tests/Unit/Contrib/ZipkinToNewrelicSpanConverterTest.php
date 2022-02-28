<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib;

use function implode;
use OpenTelemetry\Contrib\ZipkinToNewrelic\SpanConverter;
use OpenTelemetry\SDK\Attributes;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\Tests\Unit\SDK\Util\SpanData;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\Contrib\ZipkinToNewrelic\SpanConverter
 */
class ZipkinToNewrelicSpanConverterTest extends TestCase
{
    public function test_should_convert_a_span_to_a_payload_for_zipkin(): void
    {
        $span = (new SpanData())
            ->setName('guard.validate')
            ->addAttribute('service', 'guard')
            ->setResource(
                ResourceInfo::create(
                    new Attributes([
                        'telemetry.sdk.name' => 'opentelemetry',
                        'telemetry.sdk.language' => 'php',
                        'telemetry.sdk.version' => 'dev',
                    ])
                )
            )
            ->addEvent('validators.list', new Attributes(['job' => 'stage.updateTime']), 1505855799433901068)
            ->setHasEnded(true);

        $converter = new SpanConverter('test.name');
        $row = $converter->convert([$span])[0];

        $this->assertSame($span->getContext()->getSpanId(), $row['id']);
        $this->assertSame($span->getContext()->getTraceId(), $row['traceId']);

        $this->assertSame('test.name', $row['localEndpoint']['serviceName']);
        $this->assertSame($span->getName(), $row['name']);

        $this->assertSame(1505855794194009, $row['timestamp']);
        $this->assertSame(5271717, $row['duration']);

        $this->assertCount(6, $row['tags']);

        $attribute = $span->getAttributes()->get('service');
        $this->assertSame($attribute, $row['tags']['service']);

        $this->assertSame('opentelemetry', $row['tags']['telemetry.sdk.name']);
        $this->assertSame('php', $row['tags']['telemetry.sdk.language']);
        $this->assertSame('dev', $row['tags']['telemetry.sdk.version']);

        $this->assertCount(1, $row['annotations']);
        [$annotation] = $row['annotations'];
        $this->assertSame('validators.list', $annotation['value']);
        $this->assertSame(1505855799433901, $annotation['timestamp']);
    }

    public function test_tags_are_coerced_correctly_to_strings(): void
    {
        $listOfStrings = ['string-1', 'string-2'];
        $listOfNumbers = [1, 2, 3, 3.1415, 42];
        $listOfBooleans = [true, true, false, true];
        $listOfRandoms = [true, [1, 2, 3], false, 'string-1', 3.1415];

        $span = (new SpanData())
            ->setName('tags.test')
            ->addAttribute('string', 'string')
            ->addAttribute('integer-1', 1024)
            ->addAttribute('integer-2', 0)
            ->addAttribute('float', '1.2345')
            ->addAttribute('boolean-1', true)
            ->addAttribute('boolean-2', false)
            ->addAttribute('list-of-strings', $listOfStrings)
            ->addAttribute('list-of-numbers', $listOfNumbers)
            ->addAttribute('list-of-booleans', $listOfBooleans)
            ->addAttribute('list-of-random', $listOfRandoms);

        $tags = (new SpanConverter('tags.test'))->convert([$span])[0]['tags'];

        // Check that we can convert all attributes to tags
        $this->assertCount(12, $tags);

        // Tags destined for Zipkin must be pairs of strings
        foreach ($tags as $tagKey => $tagValue) {
            $this->assertIsString($tagKey);
            $this->assertIsString($tagValue);
        }

        $this->assertSame('string', $tags['string']);
        $this->assertSame('1024', $tags['integer-1']);
        $this->assertSame('0', $tags['integer-2']);
        $this->assertSame('1.2345', $tags['float']);
        $this->assertSame('true', $tags['boolean-1']);
        $this->assertSame('false', $tags['boolean-2']);

        // Lists must be casted to strings and joined with a separator
        $this->assertSame(implode(',', $listOfStrings), $tags['list-of-strings']);
        $this->assertSame(implode(',', $listOfNumbers), $tags['list-of-numbers']);
        $this->assertSame('true,true,false,true', $tags['list-of-booleans']);

        // This currently works, but OpenTelemetry\Trace\Span should stop arrays
        // containing multiple value types from being passed to the Exporter.
        $this->assertSame('true,1,2,3,false,string-1,3.1415', $tags['list-of-random']);
    }
}
