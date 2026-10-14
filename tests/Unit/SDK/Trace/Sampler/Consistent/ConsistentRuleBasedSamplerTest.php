<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace\Sampler\Consistent;

use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ConsistentFixedThresholdSampler;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ConsistentSampler;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ConsistentSamplingUtil;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\Predicate;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\PredicatedSampler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\OpenTelemetry\SDK\Trace\Sampler\Consistent\ConsistentRuleBasedSampler::class)]
class ConsistentRuleBasedSamplerTest extends TestCase
{
    private static function matchSpanName(string $nameToMatch): Predicate
    {
        return new Predicate(
            fn ($ctx, string $name) => $name === $nameToMatch,
            "spanName={$nameToMatch}",
        );
    }

    private function getThreshold(\OpenTelemetry\SDK\Trace\Sampler\Consistent\ConsistentRuleBasedSampler $sampler, string $spanName, int $spanKind = SpanKind::KIND_SERVER): int
    {
        return $sampler->getSamplingIntent(
            Context::getRoot(),
            $spanName,
            $spanKind,
            Attributes::create([]),
            [],
        )->getThreshold();
    }

    public function test_empty_rule_set_drops(): void
    {
        $sampler = ConsistentSampler::ruleBased(SpanKind::KIND_SERVER);
        $intent = $sampler->getSamplingIntent(Context::getRoot(), 'span', SpanKind::KIND_SERVER, Attributes::create([]), []);
        $this->assertSame(ConsistentSamplingUtil::INVALID_THRESHOLD, $intent->getThreshold());
    }

    public function test_no_match_drops(): void
    {
        $sampler = ConsistentSampler::ruleBased(
            null,
            PredicatedSampler::onMatch(self::matchSpanName('A'), ConsistentSampler::alwaysOn()),
        );
        $intent = $sampler->getSamplingIntent(Context::getRoot(), 'B', SpanKind::KIND_SERVER, Attributes::create([]), []);
        $this->assertSame(ConsistentSamplingUtil::INVALID_THRESHOLD, $intent->getThreshold());
    }

    public function test_first_matching_rule_used(): void
    {
        $sampler = ConsistentSampler::ruleBased(
            null,
            PredicatedSampler::onMatch(self::matchSpanName('A'), new ConsistentFixedThresholdSampler(0x80000000000000)),
            PredicatedSampler::onMatch(self::matchSpanName('B'), new ConsistentFixedThresholdSampler(0x50000000000000)),
            PredicatedSampler::onMatch(self::matchSpanName('C'), new ConsistentFixedThresholdSampler(0x30000000000000)),
        );

        $ctx = Context::getRoot();
        $attrs = Attributes::create([]);

        $intentA = $sampler->getSamplingIntent($ctx, 'A', SpanKind::KIND_CLIENT, $attrs, []);
        $this->assertSame(0x80000000000000, $intentA->getThreshold());

        $intentB = $sampler->getSamplingIntent($ctx, 'B', SpanKind::KIND_PRODUCER, $attrs, []);
        $this->assertSame(0x50000000000000, $intentB->getThreshold());

        $intentC = $sampler->getSamplingIntent($ctx, 'C', SpanKind::KIND_SERVER, $attrs, []);
        $this->assertSame(0x30000000000000, $intentC->getThreshold());

        $intentD = $sampler->getSamplingIntent($ctx, 'D', SpanKind::KIND_CONSUMER, $attrs, []);
        $this->assertSame(ConsistentSamplingUtil::INVALID_THRESHOLD, $intentD->getThreshold());
    }

    public function test_span_kind_filter_restricts_matching(): void
    {
        $sampler = ConsistentSampler::ruleBased(
            SpanKind::KIND_CLIENT,
            PredicatedSampler::onMatch(Predicate::anySpan(), ConsistentSampler::alwaysOn()),
        );

        $ctx = Context::getRoot();
        $attrs = Attributes::create([]);

        // Not CLIENT → drop
        $intent = $sampler->getSamplingIntent($ctx, 'span', SpanKind::KIND_CONSUMER, $attrs, []);
        $this->assertSame(ConsistentSamplingUtil::INVALID_THRESHOLD, $intent->getThreshold());

        // CLIENT → sampled
        $intent = $sampler->getSamplingIntent($ctx, 'span', SpanKind::KIND_CLIENT, $attrs, []);
        $this->assertSame(ConsistentSamplingUtil::MIN_THRESHOLD, $intent->getThreshold());
    }

    public function test_predicate_is_root_span(): void
    {
        // Root span: no valid parent
        $isRoot = Predicate::isRootSpan();
        $this->assertTrue($isRoot->spanMatches(Context::getRoot(), 'span', SpanKind::KIND_SERVER, Attributes::create([]), []));
    }

    public function test_predicate_any_span(): void
    {
        $anySpan = Predicate::anySpan();
        $this->assertTrue($anySpan->spanMatches(Context::getRoot(), 'span', SpanKind::KIND_SERVER, Attributes::create([]), []));
    }

    public function test_predicate_and(): void
    {
        $always = Predicate::anySpan();
        $never = new class() implements \OpenTelemetry\SDK\Trace\Sampler\Consistent\PredicateInterface {
            #[\Override]
            public function spanMatches(ContextInterface $parentContext, string $spanName, int $spanKind, AttributesInterface $attributes, array $links): bool
            {
                return false;
            }
        };

        $andPred = Predicate::and($always, $never);
        $this->assertFalse($andPred->spanMatches(Context::getRoot(), 'span', SpanKind::KIND_SERVER, Attributes::create([]), []));

        $andPred2 = Predicate::and($always, $always);
        $this->assertTrue($andPred2->spanMatches(Context::getRoot(), 'span', SpanKind::KIND_SERVER, Attributes::create([]), []));
    }

    public function test_predicate_or(): void
    {
        $always = Predicate::anySpan();
        $never = new class() implements \OpenTelemetry\SDK\Trace\Sampler\Consistent\PredicateInterface {
            #[\Override]
            public function spanMatches(ContextInterface $parentContext, string $spanName, int $spanKind, AttributesInterface $attributes, array $links): bool
            {
                return false;
            }
        };

        $orPred = Predicate::or($always, $never);
        $this->assertTrue($orPred->spanMatches(Context::getRoot(), 'span', SpanKind::KIND_SERVER, Attributes::create([]), []));
    }

    public function test_predicate_not(): void
    {
        $always = Predicate::anySpan();
        $notAlways = Predicate::not($always);
        $this->assertFalse($notAlways->spanMatches(Context::getRoot(), 'span', SpanKind::KIND_SERVER, Attributes::create([]), []));
    }

    public function test_description_includes_predicates(): void
    {
        $sampler = ConsistentSampler::ruleBased(
            null,
            PredicatedSampler::onMatch(Predicate::isRootSpan(), ConsistentSampler::alwaysOn()),
            PredicatedSampler::onMatch(Predicate::anySpan(), ConsistentSampler::alwaysOff()),
        );

        $this->assertSame(
            'ConsistentRuleBasedSampler{isRootSpan->ConsistentAlwaysOnSampler,anySpan->ConsistentAlwaysOffSampler}',
            $sampler->getDescription(),
        );
    }
}
