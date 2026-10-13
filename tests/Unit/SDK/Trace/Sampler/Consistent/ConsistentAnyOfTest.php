<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Trace\Sampler\Consistent;

use InvalidArgumentException;
use OpenTelemetry\API\Trace\TraceStateInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ComposableInterface;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ConsistentAnyOf;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ConsistentSampler;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\ConsistentSamplingUtil;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\SamplingIntent;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\SamplingIntentInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConsistentAnyOf::class)]
class ConsistentAnyOfTest extends TestCase
{
    private static function makeSampler(int $threshold, bool $reliable = true): ComposableInterface
    {
        return new class($threshold, $reliable) implements ComposableInterface {
            public function __construct(
                private readonly int $threshold,
                private readonly bool $reliable,
            ) {
            }

            #[\Override]
            public function getSamplingIntent(ContextInterface $parentContext, string $spanName, int $spanKind, AttributesInterface $attributes, array $links): SamplingIntentInterface
            {
                return new SamplingIntent($this->threshold, $this->reliable);
            }

            #[\Override]
            public function getDescription(): string
            {
                return 'Mock';
            }
        };
    }

    private function getIntent(ConsistentAnyOf $sampler): SamplingIntentInterface
    {
        return $sampler->getSamplingIntent(
            Context::getRoot(),
            'span',
            \OpenTelemetry\API\Trace\SpanKind::KIND_SERVER,
            Attributes::create([]),
            [],
        );
    }

    public function test_empty_delegates_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ConsistentAnyOf();
    }

    public function test_minimum_threshold_with_reliable(): void
    {
        $sampler = new ConsistentAnyOf(
            self::makeSampler(0x80000000000000, false), // not reliable
            self::makeSampler(0x30000000000000, true),  // reliable, lower threshold
            self::makeSampler(0xa0000000000000, false), // not relevant
            self::makeSampler(0x30000000000000, false), // same threshold, not reliable
        );

        $intent = $this->getIntent($sampler);
        $this->assertSame(0x30000000000000, $intent->getThreshold());
        $this->assertTrue($intent->isAdjustedCountReliable()); // at least one with min threshold is reliable
    }

    public function test_minimum_threshold_without_reliable(): void
    {
        $sampler = new ConsistentAnyOf(
            self::makeSampler(0x80000000000000, true),  // not the minimum
            self::makeSampler(0x30000000000000, false), // minimum but not reliable
            self::makeSampler(0xa0000000000000, true),  // not the minimum
        );

        $intent = $this->getIntent($sampler);
        $this->assertSame(0x30000000000000, $intent->getThreshold());
        $this->assertFalse($intent->isAdjustedCountReliable()); // nobody with min threshold is reliable
    }

    public function test_all_drop_gives_invalid_threshold(): void
    {
        $sampler = new ConsistentAnyOf(ConsistentSampler::alwaysOff());
        $intent = $this->getIntent($sampler);
        $this->assertSame(ConsistentSamplingUtil::INVALID_THRESHOLD, $intent->getThreshold());
        $this->assertFalse($intent->isAdjustedCountReliable());
    }

    public function test_attributes_merged_from_all_delegates(): void
    {
        $d1 = new class() implements ComposableInterface {
            #[\Override]
            public function getSamplingIntent(ContextInterface $parentContext, string $spanName, int $spanKind, AttributesInterface $attributes, array $links): SamplingIntentInterface
            {
                return new SamplingIntent(0x30000000000000, true, ['tag1' => 'a']);
            }

            #[\Override]
            public function getDescription(): string
            {
                return 'D1';
            }
        };
        $d2 = new class() implements ComposableInterface {
            #[\Override]
            public function getSamplingIntent(ContextInterface $parentContext, string $spanName, int $spanKind, AttributesInterface $attributes, array $links): SamplingIntentInterface
            {
                return new SamplingIntent(0x50000000000000, true, ['tag2' => 'b']);
            }

            #[\Override]
            public function getDescription(): string
            {
                return 'D2';
            }
        };

        $sampler = new ConsistentAnyOf($d1, $d2);
        $intent = $this->getIntent($sampler);

        $attribs = [];
        foreach ($intent->getAttributes() as $k => $v) {
            $attribs[$k] = $v;
        }

        $this->assertSame('a', $attribs['tag1'] ?? null);
        $this->assertSame('b', $attribs['tag2'] ?? null);
        $this->assertSame(0x30000000000000, $intent->getThreshold());
    }

    public function test_attribute_last_delegate_wins_on_conflict(): void
    {
        $d1 = new class() implements ComposableInterface {
            #[\Override]
            public function getSamplingIntent(ContextInterface $parentContext, string $spanName, int $spanKind, AttributesInterface $attributes, array $links): SamplingIntentInterface
            {
                return new SamplingIntent(0x30000000000000, true, ['shared' => 'first']);
            }

            #[\Override]
            public function getDescription(): string
            {
                return 'D1';
            }
        };
        $d2 = new class() implements ComposableInterface {
            #[\Override]
            public function getSamplingIntent(ContextInterface $parentContext, string $spanName, int $spanKind, AttributesInterface $attributes, array $links): SamplingIntentInterface
            {
                return new SamplingIntent(0x50000000000000, true, ['shared' => 'second']);
            }

            #[\Override]
            public function getDescription(): string
            {
                return 'D2';
            }
        };

        $sampler = new ConsistentAnyOf($d1, $d2);
        $intent = $this->getIntent($sampler);

        $attribs = [];
        foreach ($intent->getAttributes() as $k => $v) {
            $attribs[$k] = $v;
        }
        $this->assertSame('second', $attribs['shared']);
    }

    public function test_trace_state_chained_across_delegates(): void
    {
        $d1 = new class() implements ComposableInterface {
            #[\Override]
            public function getSamplingIntent(ContextInterface $parentContext, string $spanName, int $spanKind, AttributesInterface $attributes, array $links): SamplingIntentInterface
            {
                return new SamplingIntent(
                    0x30000000000000,
                    true,
                    [],
                    static fn (TraceStateInterface $ts) => $ts->with('vendor1', 'val1'),
                );
            }

            #[\Override]
            public function getDescription(): string
            {
                return 'D1';
            }
        };
        $d2 = new class() implements ComposableInterface {
            #[\Override]
            public function getSamplingIntent(ContextInterface $parentContext, string $spanName, int $spanKind, AttributesInterface $attributes, array $links): SamplingIntentInterface
            {
                return new SamplingIntent(
                    0x50000000000000,
                    true,
                    [],
                    static fn (TraceStateInterface $ts) => $ts->with('vendor2', 'val2'),
                );
            }

            #[\Override]
            public function getDescription(): string
            {
                return 'D2';
            }
        };

        $sampler = new ConsistentAnyOf($d1, $d2);
        $intent = $this->getIntent($sampler);
        $ts = $intent->updateTraceState(new \OpenTelemetry\API\Trace\TraceState(), true);

        $this->assertSame('val1', $ts->get('vendor1'));
        $this->assertSame('val2', $ts->get('vendor2'));
    }

    public function test_trace_state_updaters_receive_the_decision(): void
    {
        $delegate = new class() implements ComposableInterface {
            #[\Override]
            public function getSamplingIntent(ContextInterface $parentContext, string $spanName, int $spanKind, AttributesInterface $attributes, array $links): SamplingIntentInterface
            {
                return new SamplingIntent(
                    0,
                    true,
                    [],
                    static fn (TraceStateInterface $ts, bool $sampled) => $ts->with('seen', $sampled ? 'sampled' : 'dropped'),
                );
            }

            #[\Override]
            public function getDescription(): string
            {
                return 'D';
            }
        };
        $intent = $this->getIntent(new ConsistentAnyOf($delegate));

        $this->assertSame('sampled', $intent->updateTraceState(new \OpenTelemetry\API\Trace\TraceState(), true)->get('seen'));
        $this->assertSame('dropped', $intent->updateTraceState(new \OpenTelemetry\API\Trace\TraceState(), false)->get('seen'));
    }

    public function test_order_of_delegates_does_not_affect_minimum(): void
    {
        $d1 = self::makeSampler(0x80000000000000, false);
        $d2 = self::makeSampler(0x30000000000000, true);
        $d3 = self::makeSampler(0xa0000000000000, false);
        $d4 = self::makeSampler(0x30000000000000, false);

        // Original order
        $s1 = new ConsistentAnyOf($d1, $d2, $d3, $d4);
        $i1 = $this->getIntent($s1);

        // Reversed order
        $s2 = new ConsistentAnyOf($d4, $d3, $d2, $d1);
        $i2 = $this->getIntent($s2);

        $this->assertSame($i1->getThreshold(), $i2->getThreshold());
        $this->assertSame($i1->isAdjustedCountReliable(), $i2->isAdjustedCountReliable());
    }
}
