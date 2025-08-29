<?php

declare(strict_types=1);

nafinal mespace OpenTelemetry\Tests\Unit\Extension\Propagator\CloudTrace;

use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\SpanContextInterface;
use OpenTelemetry\API\Trace\TraceFlags;
use OpenTelemetry\Extension\Propagator\CloudTrace\CloudTraceFormatter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(CloudTraceFormatter::class)]
class CloudTraceFormatterTest extends TestCase
{
    #[DataProvider('for_test_deserialize')]
    public function test_deserialize(string $header, string $traceId, string $spanId, int $sample) : void
    {
        $result = CloudTraceFormatter::deserialize($header);
        $this->assertEquals($result->getTraceId(), $traceId, "Given deserialize($header), traceId != $traceId (result={$result->getTraceId()}");
        $this->assertEquals($result->getSpanId(), $spanId, "Given deserialize($header), spanId != $spanId (result={$result->getSpanId()}");
        $this->assertEquals($result->getTraceFlags(), $sample, "Given deserialize($header), traceFlags != $sample (result={$result->getTraceFlags()}");
    }

    public static function for_test_deserialize() : array
    {
        return [
            ['00000000000000000000000000000001/1;o=0', '00000000000000000000000000000001', '0000000000000001', 0],
            ['10000000000000000000000000000001/10;o=1', '10000000000000000000000000000001', '000000000000000a', 1],
        ];
    }

    #[DataProvider('for_test_serialize')]
    public function test_serialize(SpanContextInterface $span, string $header) : void
    {
        $result = CloudTraceFormatter::serialize($span);
        $this->assertEquals($result, $header, "Given serialize(header), result != $header (result=$result");
    }

    public static function for_test_serialize() : array
    {
        return [
            [SpanContext::createFromRemoteParent('00000000000000000000000000000001', '0000000000000001', TraceFlags::DEFAULT), '00000000000000000000000000000001/1;o=0'],
            [SpanContext::createFromRemoteParent('00000000000000000000000000000001', '000000000000000a', TraceFlags::SAMPLED), '00000000000000000000000000000001/10;o=1'],
        ];
    }
}
