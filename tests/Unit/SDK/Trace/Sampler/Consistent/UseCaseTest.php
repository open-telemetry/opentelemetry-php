<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace\Sampler\Consistent;

use OpenTelemetry\API\Trace\NonRecordingSpan;
use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\TraceFlags;
use OpenTelemetry\API\Trace\TraceState;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ConsistentSampler;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ConsistentSamplingUtil;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\OtelTraceState;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\Predicate;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\PredicatedSampler;
use OpenTelemetry\SDK\Trace\SamplingResult;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * Integration-style use case tests, ported from Java's UseCaseTest.
 *
 * Scenario: parent-based sampling with rule-based root sampler:
 * - health-check spans are NEVER sampled
 * - checkout spans are ALWAYS sampled
 * - everything else: 10% sampling
 */
#[CoversNothing]
class UseCaseTest extends TestCase
{
    private const TRACE_ID = '4bf92f3577b34da6a3ce929d0e0e4736';
    private const SPAN_ID = '00f067aa0ba902b7';

    private static function buildSampler(): ConsistentSampler
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

        $rootSampler = ConsistentSampler::ruleBased(
            null,
            PredicatedSampler::onMatch($isHealthCheck, ConsistentSampler::alwaysOff()),
            PredicatedSampler::onMatch($isCheckout, ConsistentSampler::alwaysOn()),
            PredicatedSampler::onMatch(Predicate::anySpan(), ConsistentSampler::probabilityBased(0.1)),
        );

        return ConsistentSampler::parentBased($rootSampler);
    }

    private static function makeParentContext(bool $sampled, ?int $thresholdHex = null, ?int $rvHex = null): \OpenTelemetry\Context\ContextInterface
    {
        $state = OtelTraceState::parse('');
        if ($thresholdHex !== null) {
            $state->setThreshold($thresholdHex);
        }
        if ($rvHex !== null) {
            $state->setRandomValue($rvHex);
        }

        $serialized = $state->serialize();
        $traceState = $serialized !== '' ? (new TraceState())->with(OtelTraceState::TRACE_STATE_KEY, $serialized) : new TraceState();
        $traceFlag = $sampled ? TraceFlags::SAMPLED : TraceFlags::DEFAULT;
        $spanContext = SpanContext::createFromRemoteParent(self::TRACE_ID, self::SPAN_ID, $traceFlag, $traceState);

        return Context::getRoot()->withContextValue(new NonRecordingSpan($spanContext));
    }

    // ─── Root span tests ──────────────────────────────────────────────────────

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

    private function sampleRootSpan(ConsistentSampler $sampler, string $traceId): SamplingResult
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

    // ─── Child span tests ─────────────────────────────────────────────────────

    public function test_child_with_sampled_parent_and_threshold_is_sampled(): void
    {
        // Parent is sampled, has th=0 (always sample) → child should be sampled with th=0
        $parentCtx = self::makeParentContext(sampled: true, thresholdHex: 0, rvHex: 0x7f99aa40c02744);
        $sampler = self::buildSampler();
        $result = $sampler->shouldSample(
            $parentCtx,
            self::TRACE_ID,
            'health-check', // even health-check follows parent for child spans
            SpanKind::KIND_SERVER,
            Attributes::create([]),
            [],
        );

        // Parent is sampled with th=0 → parent sampler propagates th=0 → child is sampled
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
        // Parent sampled but no th → reliable=false → th erased in output
        $parentCtx = self::makeParentContext(sampled: true, thresholdHex: null);
        $sampler = self::buildSampler();
        $result = $sampler->shouldSample(
            $parentCtx,
            self::TRACE_ID,
            'span',
            SpanKind::KIND_SERVER,
            Attributes::create([]),
            [],
        );

        // Should be sampled (parent.sampled=true → threshold=0, reliable=false → random R)
        // Random R >= 0 is always true, so this should always be RECORD_AND_SAMPLE
        $this->assertSame(SamplingResult::RECORD_AND_SAMPLE, $result->getDecision());
        // But th should NOT be set in output (reliable=false)
        $ot = OtelTraceState::parse($result->getTraceState()?->get(OtelTraceState::TRACE_STATE_KEY));
        $this->assertFalse($ot->hasValidThreshold());
    }
}
