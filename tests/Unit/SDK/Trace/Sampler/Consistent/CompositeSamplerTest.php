<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace\Sampler\Consistent;

use OpenTelemetry\API\Trace\TraceState;
use OpenTelemetry\API\Trace\TraceStateInterface;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ComposableAlwaysOff;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ComposableAlwaysOn;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ComposableProbability;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ComposableSamplerInterface;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\CompositeSampler;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ConsistentSamplingUtil;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\SamplingIntent;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\SamplingIntentInterface;
use OpenTelemetry\SDK\Trace\SamplingResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CompositeSampler::class)]
#[CoversClass(SamplingIntent::class)]
class CompositeSamplerTest extends TestCase
{
    public function test_th_is_written_for_a_sampled_span_and_rv_is_kept(): void
    {
        $parentRv = 0x7f99aa40c02744;
        $result = SamplerTestContexts::sample(new ComposableAlwaysOn(), SamplerTestContexts::parent(sampled: true, randomValue: $parentRv));

        $ot = SamplerTestContexts::otState($result);
        $this->assertSame(SamplingResult::RECORD_AND_SAMPLE, $result->getDecision());
        $this->assertSame(0, $ot->getThreshold());
        $this->assertSame($parentRv, $ot->getRandomValue());
    }

    public function test_rv_is_not_added_when_the_parent_has_none(): void
    {
        $result = SamplerTestContexts::sample(new ComposableAlwaysOn(), SamplerTestContexts::parent(sampled: true));

        $ot = SamplerTestContexts::otState($result);
        $this->assertTrue($ot->hasValidThreshold());
        $this->assertFalse($ot->hasValidRandomValue());
    }

    public function test_th_is_erased_on_drop_and_rv_is_kept(): void
    {
        $rv = 0x7FFFFFFFFFFFFF;

        $result = SamplerTestContexts::sample(new ComposableProbability(0.5), SamplerTestContexts::parent(sampled: true, threshold: 0, randomValue: $rv));

        $ot = SamplerTestContexts::otState($result);
        $this->assertSame(SamplingResult::DROP, $result->getDecision());
        $this->assertFalse($ot->hasValidThreshold());
        $this->assertSame($rv, $ot->getRandomValue());
    }

    public function test_th_is_not_written_for_an_unreliable_intent(): void
    {
        $result = SamplerTestContexts::sample(self::composable(0, reliable: false), SamplerTestContexts::parent(sampled: true));

        $this->assertSame(SamplingResult::RECORD_AND_SAMPLE, $result->getDecision());
        $this->assertNull($result->getTraceState()?->get('ot'));
    }

    public function test_the_trace_id_randomness_is_used_without_rv(): void
    {
        $sampler = new ComposableProbability(0.5);

        $dropped = SamplerTestContexts::sample($sampler, SamplerTestContexts::rootContext(), '4bf92f3577b34da6a300000000000000');
        $sampled = SamplerTestContexts::sample($sampler, SamplerTestContexts::rootContext(), '4bf92f3577b34da6a3ffffffffffffff');

        $this->assertSame(SamplingResult::DROP, $dropped->getDecision());
        $this->assertSame(SamplingResult::RECORD_AND_SAMPLE, $sampled->getDecision());
    }

    public function test_rv_takes_precedence_over_the_trace_id(): void
    {
        $sampler = new ComposableProbability(0.5);

        $dropped = SamplerTestContexts::sample($sampler, SamplerTestContexts::parent(sampled: true, randomValue: 0x7FFFFFFFFFFFFF), '4bf92f3577b34da6a3ffffffffffffff');
        $sampled = SamplerTestContexts::sample($sampler, SamplerTestContexts::parent(sampled: true, randomValue: 0x80000000000000), '4bf92f3577b34da6a300000000000000');

        $this->assertSame(SamplingResult::DROP, $dropped->getDecision());
        $this->assertSame(SamplingResult::RECORD_AND_SAMPLE, $sampled->getDecision());
        $this->assertSame(0x80000000000000, SamplerTestContexts::otState($sampled)->getThreshold());
    }

    public function test_always_off_drops_and_erases_th(): void
    {
        $result = SamplerTestContexts::sample(new ComposableAlwaysOff(), SamplerTestContexts::parent(sampled: true, threshold: 0x80000000000000, randomValue: 0x90000000000000));

        $this->assertSame(SamplingResult::DROP, $result->getDecision());
        $this->assertFalse(SamplerTestContexts::otState($result)->hasValidThreshold());
    }

    public function test_attributes_are_added_to_sampled_spans_only(): void
    {
        $sampler = self::composable(0, attributes: ['custom' => true]);

        $sampled = SamplerTestContexts::sample($sampler, SamplerTestContexts::rootContext());
        $dropped = SamplerTestContexts::sample(self::composable(ConsistentSamplingUtil::INVALID_THRESHOLD, attributes: ['custom' => true]), SamplerTestContexts::rootContext());

        $this->assertSame(['custom' => true], $sampled->getAttributes());
        $this->assertSame([], $dropped->getAttributes());
    }

    public function test_trace_state_updater_is_called_for_dropped_spans_with_the_decision(): void
    {
        $delegate = new class() implements ComposableSamplerInterface {
            /** @var list<bool> */
            public array $decisions = [];

            #[\Override]
            public function getSamplingIntent(ContextInterface $parentContext, string $spanName, int $spanKind, AttributesInterface $attributes, array $links): SamplingIntentInterface
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

            #[\Override]
            public function getDescription(): string
            {
                return 'D';
            }
        };

        $dropped = SamplerTestContexts::sample($delegate, SamplerTestContexts::rootContext(), '00112233445566778800000000000000');
        $sampled = SamplerTestContexts::sample($delegate, SamplerTestContexts::rootContext(), '001122334455667788ffffffffffffff');

        $this->assertSame(SamplingResult::DROP, $dropped->getDecision());
        $this->assertSame('dropped', SamplerTestContexts::traceState($dropped)->get('vendor'));
        $this->assertSame(SamplingResult::RECORD_AND_SAMPLE, $sampled->getDecision());
        $this->assertSame('sampled', SamplerTestContexts::traceState($sampled)->get('vendor'));
        $this->assertSame([false, true], $delegate->decisions);
    }

    public function test_th_is_added_to_a_full_trace_state_by_evicting_the_rightmost_member(): void
    {
        $context = SamplerTestContexts::withTraceState(SamplerTestContexts::traceStateWithMembers(32));

        $result = SamplerTestContexts::sample(new ComposableAlwaysOn(), $context);

        $traceState = SamplerTestContexts::traceState($result);
        $this->assertSame(SamplingResult::RECORD_AND_SAMPLE, $result->getDecision());
        $this->assertSame('th:0', $traceState->get('ot'));
        $this->assertSame(32, $traceState->getListMemberCount());
        $this->assertNull($traceState->get('k32'));
        $this->assertSame('v31', $traceState->get('k31'));
        $this->assertStringStartsWith('ot=th:0,k1=v1,', $traceState->toString());
    }

    public function test_full_trace_state_that_already_has_ot_is_updated_in_place(): void
    {
        $traceState = SamplerTestContexts::traceStateWithMembers(31)->with('ot', 'th:8');
        $this->assertSame(32, $traceState->getListMemberCount());

        $result = SamplerTestContexts::sample(new ComposableAlwaysOn(), SamplerTestContexts::withTraceState($traceState));

        $this->assertSame('th:0', SamplerTestContexts::traceState($result)->get('ot'));
        $this->assertSame(32, SamplerTestContexts::traceState($result)->getListMemberCount());
        $this->assertSame('v31', SamplerTestContexts::traceState($result)->get('k31'));
    }

    public function test_trace_state_below_the_limit_keeps_all_members(): void
    {
        $result = SamplerTestContexts::sample(new ComposableAlwaysOn(), SamplerTestContexts::withTraceState(SamplerTestContexts::traceStateWithMembers(31)));

        $this->assertSame(32, SamplerTestContexts::traceState($result)->getListMemberCount());
        $this->assertSame('v31', SamplerTestContexts::traceState($result)->get('k31'));
        $this->assertSame('th:0', SamplerTestContexts::traceState($result)->get('ot'));
    }

    public function test_dropped_span_keeps_a_full_trace_state_untouched(): void
    {
        $result = SamplerTestContexts::sample(new ComposableAlwaysOff(), SamplerTestContexts::withTraceState(SamplerTestContexts::traceStateWithMembers(32)));

        $this->assertSame(SamplingResult::DROP, $result->getDecision());
        $this->assertSame(32, SamplerTestContexts::traceState($result)->getListMemberCount());
        $this->assertNull(SamplerTestContexts::traceState($result)->get('ot'));
    }

    public function test_malformed_ot_member_does_not_discard_other_valid_sub_keys(): void
    {
        $traceState = (new TraceState())->with('ot', 'th:8;RV:1;rv:1234567890abcd;x:1');

        $result = SamplerTestContexts::sample(new ComposableAlwaysOn(), SamplerTestContexts::withTraceState($traceState));

        $this->assertSame('th:0;rv:1234567890abcd;x:1', SamplerTestContexts::traceState($result)->get('ot'));
    }

    public function test_wraps_a_composable_sampler_that_is_not_a_sampler(): void
    {
        $sampler = new CompositeSampler(self::composable(0x80000000000000));

        $this->assertSame('PlainComposable', $sampler->getDescription());
    }

    /**
     * @param array<non-empty-string, mixed> $attributes
     */
    private static function composable(int $threshold, bool $reliable = true, array $attributes = []): ComposableSamplerInterface
    {
        return new class($threshold, $reliable, $attributes) implements ComposableSamplerInterface {
            /** @param array<non-empty-string, mixed> $attributes */
            public function __construct(
                private readonly int $threshold,
                private readonly bool $reliable,
                private readonly array $attributes,
            ) {
            }

            #[\Override]
            public function getSamplingIntent(ContextInterface $parentContext, string $spanName, int $spanKind, AttributesInterface $attributes, array $links): SamplingIntentInterface
            {
                return new SamplingIntent($this->threshold, $this->reliable, $this->attributes);
            }

            #[\Override]
            public function getDescription(): string
            {
                return 'PlainComposable';
            }
        };
    }
}
