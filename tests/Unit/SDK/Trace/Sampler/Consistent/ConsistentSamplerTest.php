<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Trace\Sampler\Consistent;

use OpenTelemetry\API\Trace\NonRecordingSpan;
use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\TraceFlags;
use OpenTelemetry\API\Trace\TraceState;
use OpenTelemetry\API\Trace\TraceStateInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ComposableInterface;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ConsistentFixedThresholdSampler;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ConsistentSampler;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ConsistentSamplingUtil;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\OtelTraceState;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\SamplingIntent;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\SamplingIntentInterface;
use OpenTelemetry\SDK\Trace\SamplingResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConsistentSampler::class)]
class ConsistentSamplerTest extends TestCase
{
    private const TRACE_ID = '00112233445566778800000000000000';
    private const SPAN_ID = '0123456789abcdef';

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private static function createParentContext(
        bool $sampled,
        ?int $threshold = null,
        ?int $randomValue = null,
        bool $isRemote = false,
    ): \OpenTelemetry\Context\ContextInterface {
        $state = OtelTraceState::parse('');
        if ($threshold !== null) {
            $state->setThreshold($threshold);
        }
        if ($randomValue !== null) {
            $state->setRandomValue($randomValue);
        }

        $serialized = $state->serialize();
        $traceState = $serialized !== ''
            ? (new TraceState())->with(OtelTraceState::TRACE_STATE_KEY, $serialized)
            : new TraceState();

        $traceFlag = $sampled ? TraceFlags::SAMPLED : TraceFlags::DEFAULT;

        $spanContext = $isRemote
            ? SpanContext::createFromRemoteParent(self::TRACE_ID, self::SPAN_ID, $traceFlag, $traceState)
            : SpanContext::create(self::TRACE_ID, self::SPAN_ID, $traceFlag, $traceState);

        return Context::getRoot()->withContextValue(new NonRecordingSpan($spanContext));
    }

    private static function getOutputOtelTraceState(SamplingResult $result, \OpenTelemetry\Context\ContextInterface $parentContext): OtelTraceState
    {
        $parentSpan = \OpenTelemetry\SDK\Trace\Span::fromContext($parentContext);
        $parentTraceState = $parentSpan->getContext()->getTraceState() ?? new TraceState();
        $newTraceState = $result->getTraceState();
        $otStr = $newTraceState?->get(OtelTraceState::TRACE_STATE_KEY);

        return OtelTraceState::parse($otStr);
    }

    private static function fullTraceState(int $members): TraceStateInterface
    {
        $traceState = new TraceState();
        for ($i = $members; $i >= 1; $i--) {
            $traceState = $traceState->with('k' . $i, 'v' . $i);
        }

        return $traceState;
    }

    private static function createContextWithTraceState(TraceStateInterface $traceState): \OpenTelemetry\Context\ContextInterface
    {
        $spanContext = SpanContext::createFromRemoteParent(self::TRACE_ID, self::SPAN_ID, TraceFlags::SAMPLED, $traceState);

        return Context::getRoot()->withContextValue(new NonRecordingSpan($spanContext));
    }

    private function sample(ConsistentSampler $sampler, \OpenTelemetry\Context\ContextInterface $parentContext, string $traceId = self::TRACE_ID): SamplingResult
    {
        return $sampler->shouldSample(
            $parentContext,
            $traceId,
            'test-span',
            SpanKind::KIND_SERVER,
            Attributes::create([]),
            [],
        );
    }

    // ─── Tests ────────────────────────────────────────────────────────────────

    public function test_always_on_sampled_no_parent_rv(): void
    {
        $parentContext = self::createParentContext(sampled: true);
        $sampler = ConsistentSampler::alwaysOn();
        $result = $this->sample($sampler, $parentContext);

        $this->assertSame(SamplingResult::RECORD_AND_SAMPLE, $result->getDecision());

        $outState = self::getOutputOtelTraceState($result, $parentContext);
        $this->assertTrue($outState->hasValidThreshold());
        $this->assertSame(0, $outState->getThreshold()); // th=0
        $this->assertFalse($outState->hasValidRandomValue()); // rv not added
    }

    public function test_always_on_sampled_with_parent_rv(): void
    {
        $parentRv = 0x7f99aa40c02744;
        $parentContext = self::createParentContext(sampled: true, randomValue: $parentRv);
        $sampler = ConsistentSampler::alwaysOn();
        $result = $this->sample($sampler, $parentContext);

        $this->assertSame(SamplingResult::RECORD_AND_SAMPLE, $result->getDecision());

        $outState = self::getOutputOtelTraceState($result, $parentContext);
        $this->assertTrue($outState->hasValidThreshold());
        $this->assertSame(0, $outState->getThreshold());
        $this->assertTrue($outState->hasValidRandomValue());
        $this->assertSame($parentRv, $outState->getRandomValue()); // rv preserved unchanged
    }

    public function test_max_threshold_drops_span(): void
    {
        $parentContext = self::createParentContext(sampled: true);
        $sampler = new ConsistentFixedThresholdSampler(ConsistentSamplingUtil::MAX_THRESHOLD);
        $result = $this->sample($sampler, $parentContext);

        $this->assertSame(SamplingResult::DROP, $result->getDecision());

        $outState = self::getOutputOtelTraceState($result, $parentContext);
        $this->assertFalse($outState->hasValidThreshold()); // th erased
    }

    public function test_half_threshold_not_sampled(): void
    {
        // rv = 0x7FFFFFFFFFFFFF < 0x80000000000000 → DROP
        $rv = 0x7FFFFFFFFFFFFF;
        $parentContext = self::createParentContext(sampled: true, randomValue: $rv);
        $sampler = new ConsistentFixedThresholdSampler(0x80000000000000);
        $result = $this->sample($sampler, $parentContext);

        $this->assertSame(SamplingResult::DROP, $result->getDecision());

        $outState = self::getOutputOtelTraceState($result, $parentContext);
        $this->assertFalse($outState->hasValidThreshold()); // th erased on drop
        $this->assertTrue($outState->hasValidRandomValue()); // rv preserved
        $this->assertSame($rv, $outState->getRandomValue());
    }

    public function test_half_threshold_sampled(): void
    {
        // rv = 0x80000000000000 >= 0x80000000000000 → SAMPLE
        $rv = 0x80000000000000;
        $parentContext = self::createParentContext(sampled: true, randomValue: $rv);
        $sampler = new ConsistentFixedThresholdSampler(0x80000000000000);
        $result = $this->sample($sampler, $parentContext);

        $this->assertSame(SamplingResult::RECORD_AND_SAMPLE, $result->getDecision());

        $outState = self::getOutputOtelTraceState($result, $parentContext);
        $this->assertTrue($outState->hasValidThreshold());
        $this->assertSame(0x80000000000000, $outState->getThreshold());
        $this->assertSame($rv, $outState->getRandomValue()); // rv unchanged
    }

    public function test_parent_based_consistent_mode(): void
    {
        // Parent has both th and rv; R >= th and the parent is sampled → consistent, keep parent threshold
        $parentRv = 0x7f99aa40c02744;
        $parentTh = $parentRv; // threshold == R → sampled (T <= R)
        $parentContext = self::createParentContext(sampled: true, threshold: $parentTh, randomValue: $parentRv);
        $sampler = ConsistentSampler::parentBased(ConsistentSampler::alwaysOn());
        $result = $this->sample($sampler, $parentContext);

        $this->assertSame(SamplingResult::RECORD_AND_SAMPLE, $result->getDecision());

        $outState = self::getOutputOtelTraceState($result, $parentContext);
        $this->assertSame($parentTh, $outState->getThreshold());
        $this->assertSame($parentRv, $outState->getRandomValue());
    }

    public function test_parent_based_legacy_mode_sampled_parent(): void
    {
        // Parent has no th, but is sampled → reliable=false, th erased in output
        $parentContext = self::createParentContext(sampled: true);
        $sampler = ConsistentSampler::parentBased(ConsistentSampler::alwaysOn());
        $result = $this->sample($sampler, $parentContext);

        $this->assertSame(SamplingResult::RECORD_AND_SAMPLE, $result->getDecision());

        $outState = self::getOutputOtelTraceState($result, $parentContext);
        $this->assertFalse($outState->hasValidThreshold()); // th not set (reliable=false)
        $this->assertFalse($outState->hasValidRandomValue());
    }

    public function test_parent_based_legacy_mode_not_sampled_parent(): void
    {
        $parentContext = self::createParentContext(sampled: false);
        $sampler = ConsistentSampler::parentBased(ConsistentSampler::alwaysOn());
        $result = $this->sample($sampler, $parentContext);

        $this->assertSame(SamplingResult::DROP, $result->getDecision());
    }

    public function test_fixed_threshold_overrides_parent_th(): void
    {
        // A FixedThresholdSampler ignores the parent's th entirely and sets its own.
        $parentTh = 0x80000000000000;
        $parentRv = 0x7FFFFFFFFFFFFF;
        $parentContext = self::createParentContext(sampled: true, threshold: $parentTh, randomValue: $parentRv);

        $sampler = new ConsistentFixedThresholdSampler(0x0); // always sample
        $result = $this->sample($sampler, $parentContext);

        $this->assertSame(SamplingResult::RECORD_AND_SAMPLE, $result->getDecision());
        $outState = self::getOutputOtelTraceState($result, $parentContext);
        $this->assertSame(0, $outState->getThreshold());
        $this->assertSame($parentRv, $outState->getRandomValue()); // rv preserved
    }

    public function test_parent_based_inconsistent_th_of_sampled_parent_is_erased(): void
    {
        // Parent is sampled and has th=0x80000000000000 but rv=0x7FFFFFFFFFFFFF (rv < th → inconsistent).
        // Spec: an apparently inconsistent threshold is erased, i.e. treated as absent. The parent is
        // sampled, so the child is sampled too, but with an unknown probability (no th in the output).
        $parentTh = 0x80000000000000;
        $parentRv = 0x7FFFFFFFFFFFFF; // rv < th
        $parentContext = self::createParentContext(sampled: true, threshold: $parentTh, randomValue: $parentRv);

        $sampler = ConsistentSampler::parentBased(ConsistentSampler::alwaysOn());
        $result = $this->sample($sampler, $parentContext);

        $this->assertSame(SamplingResult::RECORD_AND_SAMPLE, $result->getDecision());
        $outState = self::getOutputOtelTraceState($result, $parentContext);
        $this->assertFalse($outState->hasValidThreshold()); // th erased
        $this->assertSame($parentRv, $outState->getRandomValue()); // rv preserved
    }

    public function test_parent_based_unsampled_parent_with_inconsistent_th_never_samples_child(): void
    {
        // Parent is NOT sampled but rv >= th (a sampled parent would be expected). The threshold is
        // inconsistent and erased; an unsampled parent without usable threshold means DROP.
        $parentContext = self::createParentContext(sampled: false, threshold: 0x80000000000000, randomValue: 0x90000000000000);

        $result = $this->sample(ConsistentSampler::parentBased(ConsistentSampler::alwaysOn()), $parentContext);

        $this->assertSame(SamplingResult::DROP, $result->getDecision());
        $this->assertFalse(self::getOutputOtelTraceState($result, $parentContext)->hasValidThreshold());
    }

    public function test_parent_based_unsampled_parent_with_consistent_th_drops_child(): void
    {
        // rv < th and the parent is not sampled: consistent, the child is dropped as well
        $parentContext = self::createParentContext(sampled: false, threshold: 0x80000000000000, randomValue: 0x70000000000000);

        $result = $this->sample(ConsistentSampler::parentBased(ConsistentSampler::alwaysOn()), $parentContext);

        $this->assertSame(SamplingResult::DROP, $result->getDecision());
    }

    public function test_parent_based_consistency_check_uses_trace_id_randomness_without_rv(): void
    {
        // TRACE_ID ends in 14 zero hex digits → R = 0. With th > 0 a sampled parent is inconsistent (R < th).
        $parentContext = self::createParentContext(sampled: true, threshold: 0x80000000000000);

        $result = $this->sample(ConsistentSampler::parentBased(ConsistentSampler::alwaysOn()), $parentContext);

        $this->assertSame(SamplingResult::RECORD_AND_SAMPLE, $result->getDecision());
        $this->assertFalse(self::getOutputOtelTraceState($result, $parentContext)->hasValidThreshold(), 'inconsistent th erased');
    }

    public function test_trace_state_updater_is_called_for_dropped_spans_with_the_decision(): void
    {
        $delegate = new class() implements ComposableInterface {
            /** @var list<bool> */
            public array $decisions = [];

            public function getSamplingIntent($ctx, $name, $kind, $attrs, $links): SamplingIntentInterface
            {
                return new SamplingIntent(
                    ConsistentSamplingUtil::MAX_THRESHOLD - 1,
                    true,
                    [],
                    function (TraceStateInterface $ts, bool $sampled) {
                        $this->decisions[] = $sampled;

                        return $ts->with('vendor', $sampled ? 'sampled' : 'dropped');
                    },
                );
            }

            public function getDescription(): string
            {
                return 'D';
            }
        };
        $sampler = ConsistentSampler::parentBased($delegate);

        // rv = 0 < th → dropped; rv = max → sampled
        $dropped = $this->sample($sampler, Context::getRoot()->withContextValue(new NonRecordingSpan(SpanContext::getInvalid())), traceId: '00112233445566778800000000000000');
        $sampled = $this->sample($sampler, Context::getRoot()->withContextValue(new NonRecordingSpan(SpanContext::getInvalid())), traceId: '001122334455667788ffffffffffffff');

        $this->assertSame(SamplingResult::DROP, $dropped->getDecision());
        $this->assertSame('dropped', $dropped->getTraceState()->get('vendor'));
        $this->assertSame(SamplingResult::RECORD_AND_SAMPLE, $sampled->getDecision());
        $this->assertSame('sampled', $sampled->getTraceState()->get('vendor'));
        $this->assertSame([false, true], $delegate->decisions);
    }

    public function test_probability_zero_is_always_off(): void
    {
        $this->assertSame(ConsistentSampler::alwaysOff(), ConsistentSampler::probabilityBased(0.0));
        $this->assertSame(
            SamplingResult::DROP,
            $this->sample(ConsistentSampler::probabilityBased(0.0), self::createParentContext(sampled: true))->getDecision(),
        );
        $this->assertNotSame(ConsistentSampler::alwaysOff(), ConsistentSampler::probabilityBased(0.5));
    }

    public function test_th_is_added_to_a_full_trace_state_by_evicting_the_rightmost_member(): void
    {
        $context = self::createContextWithTraceState(self::fullTraceState(32));

        $result = $this->sample(ConsistentSampler::alwaysOn(), $context);

        $traceState = $result->getTraceState();
        $this->assertSame(SamplingResult::RECORD_AND_SAMPLE, $result->getDecision());
        $this->assertSame('th:0', $traceState->get('ot'), 'ot was added');
        $this->assertSame(32, $traceState->getListMemberCount());
        $this->assertNull($traceState->get('k32'), 'rightmost member evicted');
        $this->assertSame('v31', $traceState->get('k31'));
        $this->assertStringStartsWith('ot=th:0,k1=v1,', $traceState->toString(), 'ot is the leftmost member');
    }

    public function test_full_trace_state_that_already_has_ot_is_updated_in_place(): void
    {
        $traceState = self::fullTraceState(31)->with('ot', 'th:8');
        $this->assertSame(32, $traceState->getListMemberCount());

        $result = $this->sample(ConsistentSampler::alwaysOn(), self::createContextWithTraceState($traceState));

        $this->assertSame('th:0', $result->getTraceState()->get('ot'));
        $this->assertSame(32, $result->getTraceState()->getListMemberCount());
        $this->assertSame('v31', $result->getTraceState()->get('k31'), 'nothing evicted');
    }

    public function test_trace_state_below_the_limit_keeps_all_members(): void
    {
        $result = $this->sample(ConsistentSampler::alwaysOn(), self::createContextWithTraceState(self::fullTraceState(31)));

        $this->assertSame(32, $result->getTraceState()->getListMemberCount());
        $this->assertSame('v31', $result->getTraceState()->get('k31'));
        $this->assertSame('th:0', $result->getTraceState()->get('ot'));
    }

    public function test_malformed_ot_member_does_not_discard_other_valid_sub_keys(): void
    {
        // 'RV:1' is invalid and is discarded; the valid rv and the other vendor's sub-key are preserved
        $traceState = (new TraceState())->with('ot', 'th:8;RV:1;rv:1234567890abcd;x:1');

        $result = $this->sample(ConsistentSampler::alwaysOn(), self::createContextWithTraceState($traceState));

        $this->assertSame('th:0;rv:1234567890abcd;x:1', $result->getTraceState()->get('ot'));
    }

    public function test_dropped_span_keeps_a_full_trace_state_untouched(): void
    {
        // th is erased on drop, there is no ot to add, so nothing must be evicted
        $result = $this->sample(ConsistentSampler::alwaysOff(), self::createContextWithTraceState(self::fullTraceState(32)));

        $this->assertSame(SamplingResult::DROP, $result->getDecision());
        $this->assertSame(32, $result->getTraceState()->getListMemberCount());
        $this->assertNull($result->getTraceState()->get('ot'));
    }

    public function test_rv_never_modified(): void
    {
        $parentRv = 0x1234567890abcd;
        $parentContext = self::createParentContext(sampled: true, randomValue: $parentRv);
        $sampler = ConsistentSampler::alwaysOn();
        $result = $this->sample($sampler, $parentContext);

        $outState = self::getOutputOtelTraceState($result, $parentContext);
        $this->assertTrue($outState->hasValidRandomValue());
        $this->assertSame($parentRv, $outState->getRandomValue());
    }

    public function test_th_erased_on_drop(): void
    {
        $rv = 0x7FFFFFFFFFFFFF;
        $parentContext = self::createParentContext(sampled: true, randomValue: $rv);
        $sampler = new ConsistentFixedThresholdSampler(0x80000000000000); // drop (R < T)
        $result = $this->sample($sampler, $parentContext);

        $this->assertSame(SamplingResult::DROP, $result->getDecision());

        $outState = self::getOutputOtelTraceState($result, $parentContext);
        $this->assertFalse($outState->hasValidThreshold());
    }

    public function test_always_off_drops(): void
    {
        $parentContext = self::createParentContext(sampled: true);
        $sampler = ConsistentSampler::alwaysOff();
        $result = $this->sample($sampler, $parentContext);

        $this->assertSame(SamplingResult::DROP, $result->getDecision());

        $outState = self::getOutputOtelTraceState($result, $parentContext);
        $this->assertFalse($outState->hasValidThreshold());
    }

    public function test_root_context_uses_trace_id_randomness(): void
    {
        // Root span (no parent) → uses last 14 chars of traceId
        // traceId last 14 chars: '00000000000000' → hexdec = 0
        $sampler = new ConsistentFixedThresholdSampler(0); // threshold=0, always sample
        $result = $sampler->shouldSample(
            Context::getRoot(),
            '4bf92f3577b34da60000000000000000',
            'span',
            SpanKind::KIND_INTERNAL,
            Attributes::create([]),
            [],
        );

        $this->assertSame(SamplingResult::RECORD_AND_SAMPLE, $result->getDecision());
    }
}
