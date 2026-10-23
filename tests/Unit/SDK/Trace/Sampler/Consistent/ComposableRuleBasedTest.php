<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace\Sampler\Consistent;

use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ComposableAlwaysOff;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ComposableAlwaysOn;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ComposableProbability;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ComposableRuleBased;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ConsistentSamplingUtil;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\Predicate;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\PredicatedSampler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ComposableRuleBased::class)]
#[CoversClass(PredicatedSampler::class)]
class ComposableRuleBasedTest extends TestCase
{
    private static function spanName(string $nameToMatch): Predicate
    {
        return new Predicate(
            static fn ($ctx, string $name) => $name === $nameToMatch,
            "spanName={$nameToMatch}",
        );
    }

    private function threshold(ComposableRuleBased $sampler, string $spanName, int $spanKind = SpanKind::KIND_SERVER): int
    {
        return $sampler->getSamplingIntent(Context::getRoot(), $spanName, $spanKind, Attributes::create([]), [])->getThreshold();
    }

    public function test_empty_rule_set_drops(): void
    {
        $this->assertSame(ConsistentSamplingUtil::INVALID_THRESHOLD, $this->threshold(new ComposableRuleBased(), 'span'));
    }

    public function test_no_match_drops(): void
    {
        $sampler = new ComposableRuleBased(new PredicatedSampler(self::spanName('A'), new ComposableAlwaysOn()));

        $intent = $sampler->getSamplingIntent(Context::getRoot(), 'B', SpanKind::KIND_SERVER, Attributes::create([]), []);

        $this->assertSame(ConsistentSamplingUtil::INVALID_THRESHOLD, $intent->getThreshold());
        $this->assertFalse($intent->isAdjustedCountReliable());
    }

    public function test_first_matching_rule_is_used(): void
    {
        $sampler = new ComposableRuleBased(
            new PredicatedSampler(self::spanName('A'), new ComposableProbability(0.5)),
            new PredicatedSampler(self::spanName('B'), new ComposableProbability(0.6875)),
            new PredicatedSampler(self::spanName('C'), new ComposableProbability(0.8125)),
            new PredicatedSampler(self::spanName('A'), new ComposableAlwaysOn()),
        );

        $this->assertSame(0x80000000000000, $this->threshold($sampler, 'A', SpanKind::KIND_CLIENT));
        $this->assertSame(0x50000000000000, $this->threshold($sampler, 'B', SpanKind::KIND_PRODUCER));
        $this->assertSame(0x30000000000000, $this->threshold($sampler, 'C'));
        $this->assertSame(ConsistentSamplingUtil::INVALID_THRESHOLD, $this->threshold($sampler, 'D', SpanKind::KIND_CONSUMER));
    }

    public function test_matching_rule_delegates_the_whole_intent(): void
    {
        $sampler = new ComposableRuleBased(
            new PredicatedSampler(self::spanName('off'), new ComposableAlwaysOff()),
            new PredicatedSampler(Predicate::anySpan(), new ComposableAlwaysOn()),
        );

        $off = $sampler->getSamplingIntent(Context::getRoot(), 'off', SpanKind::KIND_SERVER, Attributes::create([]), []);
        $on = $sampler->getSamplingIntent(Context::getRoot(), 'other', SpanKind::KIND_SERVER, Attributes::create([]), []);

        $this->assertFalse($off->isAdjustedCountReliable());
        $this->assertTrue($on->isAdjustedCountReliable());
        $this->assertSame(ConsistentSamplingUtil::MIN_THRESHOLD, $on->getThreshold());
    }

    public function test_description_lists_the_rules(): void
    {
        $sampler = new ComposableRuleBased(
            new PredicatedSampler(Predicate::isRootSpan(), new ComposableAlwaysOn()),
            new PredicatedSampler(Predicate::anySpan(), new ComposableAlwaysOff()),
        );

        $this->assertSame(
            'ComposableRuleBased{isRootSpan:ComposableAlwaysOn,anySpan:ComposableAlwaysOff}',
            $sampler->getDescription(),
        );
    }
}
