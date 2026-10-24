<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace\Sampler\Consistent;

use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ComposableAlwaysOff;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ComposableAlwaysOn;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ComposableSamplerInterface;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\CompositeSampler;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ConsistentSamplingUtil;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\SamplingIntent;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\SamplingIntentInterface;
use OpenTelemetry\SDK\Trace\SamplingResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ComposableAlwaysOn::class)]
#[CoversClass(ComposableAlwaysOff::class)]
class ComposableAlwaysOnOffTest extends TestCase
{
    private const TRACE_ID = '4bf92f3577b34da6a3ce929d0e0e4736';

    public function test_always_on_intent(): void
    {
        $intent = $this->intent(new ComposableAlwaysOn());

        $this->assertSame(ConsistentSamplingUtil::MIN_THRESHOLD, $intent->getThreshold());
        $this->assertTrue($intent->isAdjustedCountReliable());
        $this->assertSame([], $intent->getAttributes());
    }

    public function test_always_off_intent(): void
    {
        $intent = $this->intent(new ComposableAlwaysOff());

        $this->assertSame(SamplingIntent::INVALID_THRESHOLD, $intent->getThreshold());
        $this->assertFalse($intent->isAdjustedCountReliable());
        $this->assertSame([], $intent->getAttributes());
    }

    public function test_decisions_of_the_composite_sampler(): void
    {
        $this->assertSame(SamplingResult::RECORD_AND_SAMPLE, $this->decide(new ComposableAlwaysOn())->getDecision());
        $this->assertSame(SamplingResult::DROP, $this->decide(new ComposableAlwaysOff())->getDecision());
    }

    public function test_descriptions(): void
    {
        $this->assertSame('ComposableAlwaysOn', (new ComposableAlwaysOn())->getDescription());
        $this->assertSame('ComposableAlwaysOff', (new ComposableAlwaysOff())->getDescription());
    }

    private function intent(ComposableSamplerInterface $sampler): SamplingIntentInterface
    {
        return $sampler->getSamplingIntent(Context::getRoot(), 'span', SpanKind::KIND_SERVER, Attributes::create([]), []);
    }

    private function decide(ComposableSamplerInterface $sampler): SamplingResult
    {
        return (new CompositeSampler($sampler))->shouldSample(
            Context::getRoot(),
            self::TRACE_ID,
            'span',
            SpanKind::KIND_SERVER,
            Attributes::create([]),
            [],
        );
    }
}
