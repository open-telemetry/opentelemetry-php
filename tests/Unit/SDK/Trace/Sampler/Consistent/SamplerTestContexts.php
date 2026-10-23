<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace\Sampler\Consistent;

use function implode;
use OpenTelemetry\API\Trace\NonRecordingSpan;
use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\TraceFlags;
use OpenTelemetry\API\Trace\TraceState;
use OpenTelemetry\API\Trace\TraceStateInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ComposableSamplerInterface;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\CompositeSampler;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ConsistentSamplingUtil;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\OtelTraceState;
use OpenTelemetry\SDK\Trace\SamplingResult;
use PHPUnit\Framework\Assert;

/**
 * Builds parent contexts and runs a {@see CompositeSampler} for the consistent sampler tests.
 */
final class SamplerTestContexts
{
    /** The last 14 hex digits are 0, so the randomness of the trace is 0. */
    public const TRACE_ID = '00112233445566778800000000000000';
    public const SPAN_ID = '0123456789abcdef';

    private function __construct()
    {
    }

    public static function parent(bool $sampled, ?int $threshold = null, ?int $randomValue = null, bool $isRemote = false): ContextInterface
    {
        $members = [];
        if ($threshold !== null) {
            $members[] = 'th:' . ConsistentSamplingUtil::encodeThreshold($threshold);
        }
        if ($randomValue !== null) {
            $members[] = 'rv:' . ConsistentSamplingUtil::encodeRandomValue($randomValue);
        }

        $traceState = $members !== []
            ? (new TraceState())->with(OtelTraceState::TRACE_STATE_KEY, implode(';', $members))
            : new TraceState();

        return self::withTraceState($traceState, $sampled, $isRemote);
    }

    public static function withTraceState(TraceStateInterface $traceState, bool $sampled = true, bool $isRemote = true): ContextInterface
    {
        $flags = $sampled ? TraceFlags::SAMPLED : TraceFlags::DEFAULT;
        $spanContext = $isRemote
            ? SpanContext::createFromRemoteParent(self::TRACE_ID, self::SPAN_ID, $flags, $traceState)
            : SpanContext::create(self::TRACE_ID, self::SPAN_ID, $flags, $traceState);

        return Context::getRoot()->withContextValue(new NonRecordingSpan($spanContext));
    }

    public static function rootContext(): ContextInterface
    {
        return Context::getRoot()->withContextValue(new NonRecordingSpan(SpanContext::getInvalid()));
    }

    public static function traceStateWithMembers(int $members): TraceStateInterface
    {
        $traceState = new TraceState();
        for ($i = $members; $i >= 1; $i--) {
            $traceState = $traceState->with('k' . $i, 'v' . $i);
        }

        return $traceState;
    }

    public static function sample(ComposableSamplerInterface $sampler, ContextInterface $parentContext, string $traceId = self::TRACE_ID): SamplingResult
    {
        return (new CompositeSampler($sampler))->shouldSample(
            $parentContext,
            $traceId,
            'test-span',
            SpanKind::KIND_SERVER,
            Attributes::create([]),
            [],
        );
    }

    public static function traceState(SamplingResult $result): TraceStateInterface
    {
        $traceState = $result->getTraceState();
        Assert::assertNotNull($traceState);

        return $traceState;
    }

    public static function otState(SamplingResult $result): OtelTraceState
    {
        return OtelTraceState::parse($result->getTraceState()?->get(OtelTraceState::TRACE_STATE_KEY));
    }
}
