<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace\Sampler\Consistent;

use InvalidArgumentException;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ComposableProbability;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ConsistentSamplingUtil;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\SamplingIntent;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\SamplingIntentInterface;
use OpenTelemetry\SDK\Trace\SamplingResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ComposableProbability::class)]
class ComposableProbabilityTest extends TestCase
{
    #[DataProvider('thresholdProvider')]
    public function test_threshold_of_the_ratio(float $ratio, int $expectedThreshold): void
    {
        $intent = $this->intent(new ComposableProbability($ratio));

        $this->assertSame($expectedThreshold, $intent->getThreshold());
        $this->assertTrue($intent->isAdjustedCountReliable());
    }

    public static function thresholdProvider(): iterable
    {
        yield 'always' => [1.0, 0];
        yield 'half' => [0.5, 0x80000000000000];
        yield 'quarter' => [0.25, 0xc0000000000000];
        yield 'one in 2^56' => [2 ** -56, ConsistentSamplingUtil::MAX_THRESHOLD - 1];
    }

    #[DataProvider('neverSampledRatioProvider')]
    public function test_a_ratio_that_never_samples_behaves_like_always_off(float $ratio): void
    {
        $intent = $this->intent(new ComposableProbability($ratio));

        $this->assertSame(SamplingIntent::INVALID_THRESHOLD, $intent->getThreshold());
        $this->assertFalse($intent->isAdjustedCountReliable());
    }

    public static function neverSampledRatioProvider(): iterable
    {
        yield 'zero' => [0.0];
        yield 'rounds to zero' => [1e-30];
    }

    #[DataProvider('invalidRatioProvider')]
    public function test_invalid_ratio_is_rejected(float $ratio): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ComposableProbability($ratio);
    }

    public static function invalidRatioProvider(): iterable
    {
        yield 'negative' => [-0.1];
        yield 'above one' => [1.1];
        yield 'nan' => [NAN];
    }

    public function test_decisions_follow_the_randomness(): void
    {
        $sampler = new ComposableProbability(0.5);

        $dropped = SamplerTestContexts::sample($sampler, SamplerTestContexts::parent(sampled: true, randomValue: 0x7FFFFFFFFFFFFF));
        $sampled = SamplerTestContexts::sample($sampler, SamplerTestContexts::parent(sampled: true, randomValue: 0x80000000000000));

        $this->assertSame(SamplingResult::DROP, $dropped->getDecision());
        $this->assertSame(SamplingResult::RECORD_AND_SAMPLE, $sampled->getDecision());
        $this->assertSame(0x80000000000000, SamplerTestContexts::otState($sampled)->getThreshold());
    }

    public function test_ignores_the_threshold_of_the_parent(): void
    {
        $parentRv = 0x7FFFFFFFFFFFFF;
        $parent = SamplerTestContexts::parent(sampled: true, threshold: 0x80000000000000, randomValue: $parentRv);

        $result = SamplerTestContexts::sample(new ComposableProbability(1.0), $parent);

        $this->assertSame(SamplingResult::RECORD_AND_SAMPLE, $result->getDecision());
        $this->assertSame(0, SamplerTestContexts::otState($result)->getThreshold());
        $this->assertSame($parentRv, SamplerTestContexts::otState($result)->getRandomValue());
    }

    public function test_description(): void
    {
        $this->assertSame('ComposableProbability{ratio=0.25}', (new ComposableProbability(0.25))->getDescription());
    }

    private function intent(ComposableProbability $sampler): SamplingIntentInterface
    {
        return $sampler->getSamplingIntent(Context::getRoot(), 'span', SpanKind::KIND_SERVER, Attributes::create([]), []);
    }
}
