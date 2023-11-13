<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Extension\Propagator\XCloudTrace;

use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\SpanContextInterface;
use OpenTelemetry\API\Trace\TraceFlags;
use OpenTelemetry\Extension\Propagator\XCloudTrace\XCloudTraceFormatter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\Extension\Propagator\XCloudTrace\XCloudTraceFormatter
 */
class XCloudTraceFormatterTest extends TestCase
{

    /**
     * @dataProvider for_test_deserialize
     */
    public function test_deserialize(string $header, string $traceId, string $spanId, int $sample) : void
    {
        $result = XCloudTraceFormatter::deserialize($header);
        $this->assertEquals($result->getTraceId(), $traceId, "Given deserialize($header), traceId != $traceId (result={$result->getTraceId()}");
        $this->assertEquals($result->getSpanId(), $spanId, "Given deserialize($header), spanId != $spanId (result={$result->getSpanId()}");
        $this->assertEquals($result->getTraceFlags(), $sample, "Given deserialize($header), traceFlags != $sample (result={$result->getTraceFlags()}");
    }

    public function for_test_deserialize() : array
    {
        return [
            ['00000000000000000000000000000001/1;o=0', '00000000000000000000000000000001', '0000000000000001', 0],
            ['10000000000000000000000000000001/10;o=1', '10000000000000000000000000000001', '000000000000000a', 1],
        ];
    }

    /**
     * @dataProvider for_test_serialize
     */
    public function test_serialize(SpanContextInterface $span, string $header) : void
    {
        $result = XCloudTraceFormatter::serialize($span);
        $this->assertEquals($result, $header, "Given serialize(header), result != $header (result=$result");
    }

    public function for_test_serialize() : array
    {
        return [
            [SpanContext::createFromRemoteParent('00000000000000000000000000000001', '0000000000000001', TraceFlags::DEFAULT), '00000000000000000000000000000001/1;o=0'],
            [SpanContext::createFromRemoteParent('00000000000000000000000000000001', '000000000000000a', TraceFlags::SAMPLED), '00000000000000000000000000000001/10;o=1'],
        ];
    }
}
