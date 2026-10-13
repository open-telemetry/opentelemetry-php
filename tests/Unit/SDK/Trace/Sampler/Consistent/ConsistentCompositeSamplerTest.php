<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Trace\Sampler\Consistent;

use OpenTelemetry\API\Trace\NonRecordingSpan;
use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\TraceStateInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ComposableInterface;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ConsistentCompositeSampler;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ConsistentSampler;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\SamplingIntent;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\SamplingIntentInterface;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\SamplingResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConsistentCompositeSampler::class)]
final class ConsistentCompositeSamplerTest extends TestCase
{
    private static function plainComposable(int $threshold, bool $reliable = true): ComposableInterface
    {
        // Deliberately not a ConsistentSampler and not a SamplerInterface
        return new class($threshold, $reliable) implements ComposableInterface {
            public function __construct(private readonly int $threshold, private readonly bool $reliable)
            {
            }

            #[\Override]
            public function getSamplingIntent(ContextInterface $parentContext, string $spanName, int $spanKind, AttributesInterface $attributes, array $links): SamplingIntentInterface
            {
                return new SamplingIntent($this->threshold, $this->reliable, ['custom' => true]);
            }

            #[\Override]
            public function getDescription(): string
            {
                return 'PlainComposable';
            }
        };
    }

    private function sample(SamplerInterface $sampler, string $traceId): SamplingResult
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

    private static function traceState(SamplingResult $result): TraceStateInterface
    {
        $traceState = $result->getTraceState();
        self::assertNotNull($traceState);

        return $traceState;
    }

    public function test_wraps_any_composable_sampler(): void
    {
        $sampler = ConsistentSampler::composite(self::plainComposable(0x80000000000000));

        $this->assertSame('PlainComposable', $sampler->getDescription());

        $sampled = $this->sample($sampler, '0011223344556677ffffffffffffffff');
        $this->assertSame(SamplingResult::RECORD_AND_SAMPLE, $sampled->getDecision());
        $this->assertSame('th:8', self::traceState($sampled)->get('ot'));
        $this->assertSame(['custom' => true], $sampled->getAttributes());

        $dropped = $this->sample($sampler, '00112233445566770000000000000000');
        $this->assertSame(SamplingResult::DROP, $dropped->getDecision());
    }

    public function test_unreliable_intent_does_not_write_th(): void
    {
        $result = $this->sample(ConsistentSampler::composite(self::plainComposable(0, false)), '0011223344556677ffffffffffffffff');

        $this->assertSame(SamplingResult::RECORD_AND_SAMPLE, $result->getDecision());
        $this->assertNull(self::traceState($result)->get('ot'));
    }
}
