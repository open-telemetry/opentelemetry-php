<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace\Sampler\Consistent;

use function implode;
use OpenTelemetry\API\Trace\NonRecordingSpan;
use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\TraceFlags;
use OpenTelemetry\API\Trace\TraceState;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ComposableAlwaysOff;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ComposableAlwaysOn;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ComposableParentThreshold;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ComposableProbability;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ComposableRuleBased;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\CompositeSampler;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ConsistentSamplingUtil;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\OtelTraceState;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\Predicate;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\PredicatedSampler;
use OpenTelemetry\SDK\Trace\SamplingResult;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * Parent threshold sampling with a rule based root sampler: health-check spans are never sampled, checkout spans
 * are always sampled and all other root spans are sampled with a probability of 10%.
 */
#[CoversNothing]
class UseCaseTest extends TestCase
{
    private const TRACE_ID = '4bf92f3577b34da6a3ce929d0e0e4736';
    private const SPAN_ID = '00f067aa0ba902b7';

    private static function buildSampler(): CompositeSampler
    {
        $isHealthCheck = new class() implements \OpenTelemetry\SDK\Trace\Sampler\Consistent\PredicateInterface {
            #[\Override]
            public function spanMatches(ContextInterface $parentContext, string $spanName, int $spanKind, AttributesInterface $attributes, array $links): bool
            {
                return $spanName === 'health-check';
            }
        };

        $isCheckout = new class() implements \OpenTelemetry\SDK\Trace\Sampler\Consistent\PredicateInterface {
            #[\Override]
            public function spanMatches(ContextInterface $parentContext, string $spanName, int $spanKind, AttributesInterface $attributes, array $links): bool
            {
                return $spanName === 'checkout';
            }
        };

        $rootSampler = new ComposableRuleBased(
            new PredicatedSampler($isHealthCheck, new ComposableAlwaysOff()),
            new PredicatedSampler($isCheckout, new ComposableAlwaysOn()),
            new PredicatedSampler(Predicate::anySpan(), new ComposableProbability(0.1)),
        );

        return new CompositeSampler(new ComposableParentThreshold($rootSampler));
    }

    private static function makeParentContext(bool $sampled, ?int $threshold = null, ?int $randomValue = null): ContextInterface
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
        $spanContext = SpanContext::createFromRemoteParent(
            self::TRACE_ID,
            self::SPAN_ID,
            $sampled ? TraceFlags::SAMPLED : TraceFlags::DEFAULT,
            $traceState,
        );

        return Context::getRoot()->withContextValue(new NonRecordingSpan($spanContext));
    }

    public function test_root_health_check_is_dropped(): void
    {
        $sampler = self::buildSampler();
        $result = $sampler->shouldSample(
            Context::getRoot(),
            self::TRACE_ID,
            'health-check',
            SpanKind::KIND_SERVER,
            Attributes::create([]),
            [],
        );

        $this->assertSame(SamplingResult::DROP, $result->getDecision());
        $newTs = $result->getTraceState();
        $ot = OtelTraceState::parse($newTs?->get(OtelTraceState::TRACE_STATE_KEY));
        $this->assertFalse($ot->hasValidThreshold());
    }

    public function test_root_checkout_is_sampled(): void
    {
        $sampler = self::buildSampler();
        $result = $sampler->shouldSample(
            Context::getRoot(),
            self::TRACE_ID,
            'checkout',
            SpanKind::KIND_SERVER,
            Attributes::create([]),
            [],
        );

        $this->assertSame(SamplingResult::RECORD_AND_SAMPLE, $result->getDecision());
        $ot = OtelTraceState::parse($result->getTraceState()?->get(OtelTraceState::TRACE_STATE_KEY));
        $this->assertTrue($ot->hasValidThreshold());
        $this->assertSame(0, $ot->getThreshold()); // th=0 = always sample
    }

    public function test_root_other_is_sampled_with_10_percent_probability(): void
    {
        $sampler = self::buildSampler();

        // The randomness of a root span is the last 14 hex digits of the trace ID. The 10% rejection threshold is
        // 0xe6666666666666, so a lower randomness is dropped and a higher one is sampled.
        $dropped = $this->sampleRootSpan($sampler, '4bf92f3577b34da6a3ce929d0e0e4736'); // R = 0xce929d0e0e4736
        $sampled = $this->sampleRootSpan($sampler, '4bf92f3577b34da6a3ffffffffffffff'); // R = 0xffffffffffffff

        $this->assertSame(SamplingResult::DROP, $dropped->getDecision());
        $this->assertSame(SamplingResult::RECORD_AND_SAMPLE, $sampled->getDecision());
        $this->assertSame(
            ConsistentSamplingUtil::calculateThreshold(0.1),
            OtelTraceState::parse($sampled->getTraceState()?->get(OtelTraceState::TRACE_STATE_KEY))->getThreshold(),
        );
    }

    private function sampleRootSpan(CompositeSampler $sampler, string $traceId): SamplingResult
    {
        return $sampler->shouldSample(
            Context::getRoot(),
            $traceId,
            'other-span',
            SpanKind::KIND_SERVER,
            Attributes::create([]),
            [],
        );
    }

    public function test_child_with_sampled_parent_and_threshold_is_sampled(): void
    {
        // A sampled parent with th=0 gives a sampled child with th=0
        $parentCtx = self::makeParentContext(sampled: true, threshold: 0, randomValue: 0x7f99aa40c02744);
        $sampler = self::buildSampler();
        $result = $sampler->shouldSample(
            $parentCtx,
            self::TRACE_ID,
            'health-check', // even health-check follows parent for child spans
            SpanKind::KIND_SERVER,
            Attributes::create([]),
            [],
        );

        $this->assertSame(SamplingResult::RECORD_AND_SAMPLE, $result->getDecision());
        $ot = OtelTraceState::parse($result->getTraceState()?->get(OtelTraceState::TRACE_STATE_KEY));
        $this->assertSame(0, $ot->getThreshold());
    }

    public function test_child_with_not_sampled_parent_is_dropped(): void
    {
        $parentCtx = self::makeParentContext(sampled: false);
        $sampler = self::buildSampler();
        $result = $sampler->shouldSample(
            $parentCtx,
            self::TRACE_ID,
            'checkout',
            SpanKind::KIND_SERVER,
            Attributes::create([]),
            [],
        );

        $this->assertSame(SamplingResult::DROP, $result->getDecision());
    }

    public function test_child_with_sampled_parent_no_threshold_follows_sampled_flag(): void
    {
        // A sampled parent without th gives a sampled child; the adjusted count is not reliable, so th is not written
        $parentCtx = self::makeParentContext(sampled: true, threshold: null);
        $sampler = self::buildSampler();
        $result = $sampler->shouldSample(
            $parentCtx,
            self::TRACE_ID,
            'span',
            SpanKind::KIND_SERVER,
            Attributes::create([]),
            [],
        );

        $this->assertSame(SamplingResult::RECORD_AND_SAMPLE, $result->getDecision());
        $ot = OtelTraceState::parse($result->getTraceState()?->get(OtelTraceState::TRACE_STATE_KEY));
        $this->assertFalse($ot->hasValidThreshold());
    }
}
