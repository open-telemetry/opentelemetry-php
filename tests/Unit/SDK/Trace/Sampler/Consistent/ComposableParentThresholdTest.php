<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace\Sampler\Consistent;

use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\TraceState;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ComposableAlwaysOff;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ComposableAlwaysOn;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ComposableParentThreshold;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ConsistentSamplingUtil;
use OpenTelemetry\SDK\Trace\SamplingResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ComposableParentThreshold::class)]
class ComposableParentThresholdTest extends TestCase
{
    public function test_root_spans_use_the_root_sampler(): void
    {
        $sampler = new ComposableParentThreshold(new ComposableAlwaysOff());

        $intent = $sampler->getSamplingIntent(SamplerTestContexts::rootContext(), 'span', SpanKind::KIND_SERVER, Attributes::create([]), []);

        $this->assertSame(ConsistentSamplingUtil::INVALID_THRESHOLD, $intent->getThreshold());
    }

    public function test_consistent_parent_threshold_is_propagated(): void
    {
        $parentRv = 0x7f99aa40c02744;
        $parentTh = $parentRv;
        $parent = SamplerTestContexts::parent(sampled: true, threshold: $parentTh, randomValue: $parentRv);

        $result = $this->sample($parent);

        $ot = SamplerTestContexts::otState($result);
        $this->assertSame(SamplingResult::RECORD_AND_SAMPLE, $result->getDecision());
        $this->assertSame($parentTh, $ot->getThreshold());
        $this->assertSame($parentRv, $ot->getRandomValue());
    }

    public function test_sampled_parent_without_th_gives_a_sampled_child_without_th(): void
    {
        $result = $this->sample(SamplerTestContexts::parent(sampled: true));

        $ot = SamplerTestContexts::otState($result);
        $this->assertSame(SamplingResult::RECORD_AND_SAMPLE, $result->getDecision());
        $this->assertFalse($ot->hasValidThreshold());
        $this->assertFalse($ot->hasValidRandomValue());
    }

    public function test_unsampled_parent_without_th_gives_a_dropped_child(): void
    {
        $this->assertSame(SamplingResult::DROP, $this->sample(SamplerTestContexts::parent(sampled: false))->getDecision());
    }

    public function test_inconsistent_th_of_a_sampled_parent_is_erased(): void
    {
        // rv is below th, so a sampled parent is inconsistent: th is treated as absent and the child follows the flag
        $parentRv = 0x7FFFFFFFFFFFFF;
        $parent = SamplerTestContexts::parent(sampled: true, threshold: 0x80000000000000, randomValue: $parentRv);

        $result = $this->sample($parent);

        $ot = SamplerTestContexts::otState($result);
        $this->assertSame(SamplingResult::RECORD_AND_SAMPLE, $result->getDecision());
        $this->assertFalse($ot->hasValidThreshold());
        $this->assertSame($parentRv, $ot->getRandomValue());
    }

    public function test_inconsistent_th_of_an_unsampled_parent_never_samples_the_child(): void
    {
        // rv is not below th, so an unsampled parent is inconsistent
        $parent = SamplerTestContexts::parent(sampled: false, threshold: 0x80000000000000, randomValue: 0x90000000000000);

        $result = $this->sample($parent);

        $this->assertSame(SamplingResult::DROP, $result->getDecision());
        $this->assertFalse(SamplerTestContexts::otState($result)->hasValidThreshold());
    }

    public function test_consistent_th_of_an_unsampled_parent_drops_the_child(): void
    {
        $parent = SamplerTestContexts::parent(sampled: false, threshold: 0x80000000000000, randomValue: 0x70000000000000);

        $this->assertSame(SamplingResult::DROP, $this->sample($parent)->getDecision());
    }

    public function test_consistency_is_checked_against_the_trace_id_randomness_without_rv(): void
    {
        // the randomness of the trace is 0, so a sampled parent with th above 0 is inconsistent
        $parent = SamplerTestContexts::parent(sampled: true, threshold: 0x80000000000000);

        $result = $this->sample($parent);

        $this->assertSame(SamplingResult::RECORD_AND_SAMPLE, $result->getDecision());
        $this->assertFalse(SamplerTestContexts::otState($result)->hasValidThreshold());
    }

    public function test_duplicate_th_in_the_parent_is_not_trusted(): void
    {
        $traceState = (new TraceState())->with('ot', 'th:0;th:f;rv:ffffffffffffff;x:1;x:2');

        $result = $this->sample(SamplerTestContexts::withTraceState($traceState));

        $this->assertSame(SamplingResult::RECORD_AND_SAMPLE, $result->getDecision());
        $this->assertSame('rv:ffffffffffffff;x:1', SamplerTestContexts::traceState($result)->get('ot'));
    }

    public function test_description(): void
    {
        $this->assertSame(
            'ComposableParentThreshold{root=ComposableAlwaysOn}',
            (new ComposableParentThreshold(new ComposableAlwaysOn()))->getDescription(),
        );
    }

    private function sample(ContextInterface $parent): SamplingResult
    {
        return SamplerTestContexts::sample(new ComposableParentThreshold(new ComposableAlwaysOn()), $parent);
    }
}
