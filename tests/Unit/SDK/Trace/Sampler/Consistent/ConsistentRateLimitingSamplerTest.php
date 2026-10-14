<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace\Sampler\Consistent;

use InvalidArgumentException;
use OpenTelemetry\API\Trace\NonRecordingSpan;
use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\TraceStateInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ComposableInterface;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ConsistentRateLimitingSampler;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ConsistentSampler;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ConsistentSamplingUtil;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\SamplingIntent;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\SamplingIntentInterface;
use OpenTelemetry\SDK\Trace\SamplingResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConsistentRateLimitingSampler::class)]
final class ConsistentRateLimitingSamplerTest extends TestCase
{
    private const MILLISECOND = 1_000_000;

    private int $now = 0;

    /**
     * Feeds $count spans, one every $stepNanos of fake time, and returns the suggested probability of the last
     * span and the sum of all suggested probabilities (the expected number of sampled spans).
     *
     * @return array{float, float}
     */
    private function feed(ConsistentRateLimitingSampler $sampler, int $count, int $stepNanos): array
    {
        $last = 0.0;
        $sum = 0.0;
        for ($i = 0; $i < $count; $i++) {
            $this->now += $stepNanos;
            $last = ConsistentSamplingUtil::calculateSamplingProbability($this->intent($sampler)->getThreshold());
            $sum += $last;
        }

        return [$last, $sum];
    }

    private function intent(ConsistentRateLimitingSampler $sampler): SamplingIntentInterface
    {
        return $sampler->getSamplingIntent(Context::getRoot(), 'span', SpanKind::KIND_SERVER, Attributes::create([]), []);
    }

    private function create(ComposableInterface $delegate, float $limit, float $adaptationSeconds): ConsistentRateLimitingSampler
    {
        $this->now = 0;
        $sampler = ConsistentSampler::rateLimited($delegate, $limit, $adaptationSeconds);
        $this->assertInstanceOf(ConsistentRateLimitingSampler::class, $sampler);

        return new ConsistentRateLimitingSampler($delegate, $limit, $adaptationSeconds, fn (): int => $this->now);
    }

    private static function traceState(SamplingResult $result): TraceStateInterface
    {
        $traceState = $result->getTraceState();
        self::assertNotNull($traceState);

        return $traceState;
    }

    public function test_traffic_below_the_limit_keeps_the_delegate_probability(): void
    {
        // 100 spans/s, limit 1000/s
        $sampler = $this->create(ConsistentSampler::alwaysOn(), 1000.0, 1.0);

        [$last] = $this->feed($sampler, 1000, 10 * self::MILLISECOND);

        $this->assertEqualsWithDelta(1.0, $last, 1e-9);
    }

    public function test_traffic_above_the_limit_is_reduced_to_about_the_limit(): void
    {
        // 1000 spans/s for 20 s, limit 10/s → probability converges to 10 / 1000 = 0.01, about 200 sampled spans
        $sampler = $this->create(ConsistentSampler::alwaysOn(), 10.0, 1.0);

        [$last, $expectedSampled] = $this->feed($sampler, 20000, self::MILLISECOND);

        $this->assertEqualsWithDelta(0.01, $last, 0.001);
        $this->assertEqualsWithDelta(200.0, $expectedSampled, 20.0);
    }

    public function test_probability_recovers_after_the_load_drops(): void
    {
        $sampler = $this->create(ConsistentSampler::alwaysOn(), 10.0, 1.0);
        $this->feed($sampler, 20000, self::MILLISECOND);

        // 1 span/s for a minute is below the limit again
        [$last] = $this->feed($sampler, 60, 1000 * self::MILLISECOND);

        $this->assertEqualsWithDelta(1.0, $last, 0.05);
    }

    public function test_delegate_probability_is_scaled_not_replaced(): void
    {
        // Delegate samples 50%; the limiter lowers it proportionally when the limit is exceeded
        $sampler = $this->create(ConsistentSampler::probabilityBased(0.5), 10.0, 1.0);

        [$last] = $this->feed($sampler, 20000, self::MILLISECOND);

        // average delegate probability 0.5, target max probability 0.01 → 0.01 / 0.5 * 0.5 = 0.01
        $this->assertEqualsWithDelta(0.01, $last, 0.001);
    }

    public function test_cold_start_samples_almost_nothing(): void
    {
        // Documents why the sampler does not work with PHP-FPM / Apache: with an empty window (a new process
        // for every request) the first span is sampled with a probability of about limit * elapsed time.
        $sampler = $this->create(ConsistentSampler::alwaysOn(), 10.0, 1.0);

        [$first] = $this->feed($sampler, 1, self::MILLISECOND);

        $this->assertEqualsWithDelta(0.01, $first, 0.001);
    }

    public function test_clock_not_advancing_or_going_backwards_is_tolerated(): void
    {
        $sampler = $this->create(ConsistentSampler::alwaysOn(), 10.0, 1.0);
        $this->feed($sampler, 10, 10 * self::MILLISECOND);

        $this->now -= 1000 * self::MILLISECOND; // clock jumps backwards
        $first = ConsistentSamplingUtil::calculateSamplingProbability($this->intent($sampler)->getThreshold());
        $second = ConsistentSamplingUtil::calculateSamplingProbability($this->intent($sampler)->getThreshold()); // no time passes

        $this->assertGreaterThanOrEqual(0.0, $first);
        $this->assertLessThanOrEqual(1.0, $first);
        $this->assertLessThanOrEqual(1.0, $second);
    }

    public function test_dropped_delegate_stays_dropped(): void
    {
        $sampler = $this->create(ConsistentSampler::alwaysOff(), 10.0, 1.0);

        $this->now += self::MILLISECOND;

        $this->assertSame(ConsistentSamplingUtil::INVALID_THRESHOLD, $this->intent($sampler)->getThreshold());
    }

    public function test_passes_through_reliability_attributes_and_trace_state_updates(): void
    {
        $delegate = new class() implements ComposableInterface {
            #[\Override]
            public function getSamplingIntent(ContextInterface $parentContext, string $spanName, int $spanKind, AttributesInterface $attributes, array $links): SamplingIntentInterface
            {
                return new SamplingIntent(
                    0,
                    false,
                    ['a' => 1],
                    static fn (TraceStateInterface $ts, bool $sampled) => $ts->with('vendor', $sampled ? 'sampled' : 'dropped'),
                );
            }

            #[\Override]
            public function getDescription(): string
            {
                return 'D';
            }
        };
        $sampler = $this->create($delegate, 1000.0, 1.0);
        $this->now += self::MILLISECOND;

        $intent = $this->intent($sampler);

        $this->assertFalse($intent->isAdjustedCountReliable());
        $this->assertSame(['a' => 1], $intent->getAttributes());
        $this->assertSame('sampled', $intent->updateTraceState(new \OpenTelemetry\API\Trace\TraceState(), true)->get('vendor'));
    }

    public function test_decides_through_the_sampler_interface(): void
    {
        $sampler = $this->create(ConsistentSampler::alwaysOn(), 1000.0, 1.0);
        $this->now += 10 * self::MILLISECOND;

        $result = $sampler->shouldSample(
            Context::getRoot()->withContextValue(new NonRecordingSpan(SpanContext::getInvalid())),
            '0011223344556677ffffffffffffffff',
            'span',
            SpanKind::KIND_SERVER,
            Attributes::create([]),
            [],
        );

        $this->assertSame(SamplingResult::RECORD_AND_SAMPLE, $result->getDecision());
        $this->assertSame('th:0', self::traceState($result)->get('ot'));
    }

    #[DataProvider('invalidArgumentsProvider')]
    public function test_invalid_arguments_are_rejected(float $limit, float $adaptationSeconds): void
    {
        $this->expectException(InvalidArgumentException::class);

        ConsistentSampler::rateLimited(ConsistentSampler::alwaysOn(), $limit, $adaptationSeconds);
    }

    public static function invalidArgumentsProvider(): iterable
    {
        yield 'zero limit' => [0.0, 1.0];
        yield 'negative limit' => [-1.0, 1.0];
        yield 'zero adaptation time' => [10.0, 0.0];
        yield 'negative adaptation time' => [10.0, -1.0];
        yield 'NaN limit' => [NAN, 1.0];
        yield 'NaN adaptation time' => [10.0, NAN];
    }

    public function test_description(): void
    {
        $this->assertSame(
            'ConsistentRateLimitingSampler{targetSpansPerSecondLimit=10, adaptationTimeSeconds=1.5}',
            ConsistentSampler::rateLimited(ConsistentSampler::alwaysOn(), 10.0, 1.5)->getDescription(),
        );
    }
}
