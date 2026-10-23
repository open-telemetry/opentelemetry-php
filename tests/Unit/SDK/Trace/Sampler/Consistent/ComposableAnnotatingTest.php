<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace\Sampler\Consistent;

use OpenTelemetry\API\Trace\NonRecordingSpan;
use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\TraceStateInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ComposableAlwaysOff;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ComposableAlwaysOn;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ComposableAnnotating;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ComposableProbability;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ComposableSamplerInterface;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\CompositeSampler;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ConsistentSamplingUtil;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\SamplingIntent;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\SamplingIntentInterface;
use OpenTelemetry\SDK\Trace\SamplingResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ComposableAnnotating::class)]
final class ComposableAnnotatingTest extends TestCase
{
    private const TRACE_ID = '0011223344556677ffffffffffffffff';

    private function sample(ComposableSamplerInterface $sampler, string $traceId = self::TRACE_ID): SamplingResult
    {
        return (new CompositeSampler($sampler))->shouldSample(
            Context::getRoot()->withContextValue(new NonRecordingSpan(SpanContext::getInvalid())),
            $traceId,
            'span',
            SpanKind::KIND_SERVER,
            Attributes::create([]),
            [],
        );
    }

    private static function traceState(SamplingResult $result): TraceStateInterface
    {
        $traceState = $result->getTraceState();
        self::assertNotNull($traceState);

        return $traceState;
    }

    public function test_adds_attributes_to_sampled_spans(): void
    {
        $sampler = new ComposableAnnotating(['team' => 'checkout'], new ComposableAlwaysOn());

        $result = $this->sample($sampler);

        $this->assertSame(SamplingResult::RECORD_AND_SAMPLE, $result->getDecision());
        $this->assertSame(['team' => 'checkout'], $result->getAttributes());
        $this->assertSame('th:0', self::traceState($result)->get('ot'), 'delegate threshold is used');
    }

    public function test_does_not_add_attributes_to_dropped_spans(): void
    {
        $result = $this->sample(new ComposableAnnotating(['team' => 'checkout'], new ComposableAlwaysOff()));

        $this->assertSame(SamplingResult::DROP, $result->getDecision());
        $this->assertSame([], $result->getAttributes());
    }

    public function test_keeps_the_threshold_decision_of_the_delegate(): void
    {
        $sampler = new ComposableAnnotating(['a' => 1], new ComposableProbability(0.5));

        $this->assertSame(SamplingResult::DROP, $this->sample($sampler, '00112233445566770000000000000000')->getDecision());
        $this->assertSame(SamplingResult::RECORD_AND_SAMPLE, $this->sample($sampler, '0011223344556677ffffffffffffffff')->getDecision());
    }

    public function test_merges_delegate_attributes_and_own_attributes_win(): void
    {
        $delegate = new class() implements ComposableSamplerInterface {
            #[\Override]
            public function getSamplingIntent(ContextInterface $parentContext, string $spanName, int $spanKind, AttributesInterface $attributes, array $links): SamplingIntentInterface
            {
                return new SamplingIntent(0, true, ['shared' => 'delegate', 'only.delegate' => 1]);
            }

            #[\Override]
            public function getDescription(): string
            {
                return 'D';
            }
        };

        $result = $this->sample(new ComposableAnnotating(['shared' => 'own', 'only.own' => 2], $delegate));

        $this->assertSame(['shared' => 'own', 'only.delegate' => 1, 'only.own' => 2], $result->getAttributes());
    }

    public function test_delegates_trace_state_updates(): void
    {
        $delegate = new class() implements ComposableSamplerInterface {
            #[\Override]
            public function getSamplingIntent(ContextInterface $parentContext, string $spanName, int $spanKind, AttributesInterface $attributes, array $links): SamplingIntentInterface
            {
                return new SamplingIntent(
                    0,
                    true,
                    [],
                    static fn (TraceStateInterface $ts, bool $sampled) => $ts->with('vendor', $sampled ? 'sampled' : 'dropped'),
                );
            }

            #[\Override]
            public function getDescription(): string
            {
                return 'D';
            }
        };

        $result = $this->sample(new ComposableAnnotating(['a' => 1], $delegate));

        $this->assertSame('sampled', self::traceState($result)->get('vendor'));
    }

    public function test_preserves_unreliable_adjusted_count(): void
    {
        // An unreliable delegate (e.g. parent-based without th) must stay unreliable: no th is written
        $sampler = new ComposableAnnotating(['a' => 1], new class() implements ComposableSamplerInterface {
            #[\Override]
            public function getSamplingIntent(ContextInterface $parentContext, string $spanName, int $spanKind, AttributesInterface $attributes, array $links): SamplingIntentInterface
            {
                return new SamplingIntent(ConsistentSamplingUtil::MIN_THRESHOLD, false);
            }

            #[\Override]
            public function getDescription(): string
            {
                return 'D';
            }
        });

        $result = $this->sample($sampler);

        $this->assertSame(SamplingResult::RECORD_AND_SAMPLE, $result->getDecision());
        $this->assertNull(self::traceState($result)->get('ot'));
    }

    public function test_description(): void
    {
        $this->assertSame(
            'ComposableAnnotating{ComposableAlwaysOn,{"team":"checkout"}}',
            (new ComposableAnnotating(['team' => 'checkout'], new ComposableAlwaysOn()))->getDescription(),
        );
    }
}
