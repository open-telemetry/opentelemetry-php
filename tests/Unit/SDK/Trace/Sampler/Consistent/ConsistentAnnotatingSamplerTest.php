<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Trace\Sampler\Consistent;

use OpenTelemetry\API\Trace\NonRecordingSpan;
use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\TraceStateInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ComposableInterface;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ConsistentAnnotatingSampler;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ConsistentSampler;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ConsistentSamplingUtil;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\SamplingIntent;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\SamplingIntentInterface;
use OpenTelemetry\SDK\Trace\SamplingResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConsistentAnnotatingSampler::class)]
final class ConsistentAnnotatingSamplerTest extends TestCase
{
    private const TRACE_ID = '0011223344556677ffffffffffffffff';

    private function sample(ConsistentSampler $sampler, string $traceId = self::TRACE_ID): SamplingResult
    {
        return $sampler->shouldSample(
            Context::getRoot()->withContextValue(new NonRecordingSpan(SpanContext::getInvalid())),
            $traceId,
            'span',
            SpanKind::KIND_SERVER,
            Attributes::create([]),
            [],
        );
    }

    public function test_adds_attributes_to_sampled_spans(): void
    {
        $sampler = ConsistentSampler::annotating(['team' => 'checkout'], ConsistentSampler::alwaysOn());

        $result = $this->sample($sampler);

        $this->assertSame(SamplingResult::RECORD_AND_SAMPLE, $result->getDecision());
        $this->assertSame(['team' => 'checkout'], $result->getAttributes());
        $this->assertSame('th:0', $result->getTraceState()->get('ot'), 'delegate threshold is used');
    }

    public function test_does_not_add_attributes_to_dropped_spans(): void
    {
        $result = $this->sample(ConsistentSampler::annotating(['team' => 'checkout'], ConsistentSampler::alwaysOff()));

        $this->assertSame(SamplingResult::DROP, $result->getDecision());
        $this->assertSame([], $result->getAttributes());
    }

    public function test_keeps_the_threshold_decision_of_the_delegate(): void
    {
        $sampler = ConsistentSampler::annotating(['a' => 1], ConsistentSampler::probabilityBased(0.5));

        $this->assertSame(SamplingResult::DROP, $this->sample($sampler, '00112233445566770000000000000000')->getDecision());
        $this->assertSame(SamplingResult::RECORD_AND_SAMPLE, $this->sample($sampler, '0011223344556677ffffffffffffffff')->getDecision());
    }

    public function test_merges_delegate_attributes_and_own_attributes_win(): void
    {
        $delegate = new class() implements ComposableInterface {
            public function getSamplingIntent($ctx, $name, $kind, $attrs, $links): SamplingIntentInterface
            {
                return new SamplingIntent(0, true, ['shared' => 'delegate', 'only.delegate' => 1]);
            }

            public function getDescription(): string
            {
                return 'D';
            }
        };

        $result = $this->sample(ConsistentSampler::annotating(['shared' => 'own', 'only.own' => 2], $delegate));

        $this->assertSame(['shared' => 'own', 'only.delegate' => 1, 'only.own' => 2], $result->getAttributes());
    }

    public function test_delegates_trace_state_updates(): void
    {
        $delegate = new class() implements ComposableInterface {
            public function getSamplingIntent($ctx, $name, $kind, $attrs, $links): SamplingIntentInterface
            {
                return new SamplingIntent(
                    0,
                    true,
                    [],
                    static fn (TraceStateInterface $ts, bool $sampled) => $ts->with('vendor', $sampled ? 'sampled' : 'dropped'),
                );
            }

            public function getDescription(): string
            {
                return 'D';
            }
        };

        $result = $this->sample(ConsistentSampler::annotating(['a' => 1], $delegate));

        $this->assertSame('sampled', $result->getTraceState()->get('vendor'));
    }

    public function test_preserves_unreliable_adjusted_count(): void
    {
        // An unreliable delegate (e.g. parent-based without th) must stay unreliable: no th is written
        $sampler = ConsistentSampler::annotating(['a' => 1], new class() implements ComposableInterface {
            public function getSamplingIntent($ctx, $name, $kind, $attrs, $links): SamplingIntentInterface
            {
                return new SamplingIntent(ConsistentSamplingUtil::MIN_THRESHOLD, false);
            }

            public function getDescription(): string
            {
                return 'D';
            }
        });

        $result = $this->sample($sampler);

        $this->assertSame(SamplingResult::RECORD_AND_SAMPLE, $result->getDecision());
        $this->assertNull($result->getTraceState()->get('ot'));
    }

    public function test_description(): void
    {
        $this->assertSame(
            'ConsistentAnnotatingSampler{ConsistentAlwaysOnSampler,{"team":"checkout"}}',
            ConsistentSampler::annotating(['team' => 'checkout'], ConsistentSampler::alwaysOn())->getDescription(),
        );
    }
}
