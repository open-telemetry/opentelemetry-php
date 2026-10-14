<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace\Sampler\Consistent;

use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\Predicate;
use OpenTelemetry\SDK\Trace\Sampler\Consistent\PredicateInterface;
use OpenTelemetry\SDK\Trace\Span;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Predicate::class)]
final class PredicateTest extends TestCase
{
    private const TRACE_ID = '0af7651916cd43dd8448eb211c80319c';
    private const SPAN_ID = 'b7ad6b7169203331';

    public function test_span_kind_in(): void
    {
        $p = Predicate::spanKindIn(SpanKind::KIND_SERVER, SpanKind::KIND_CONSUMER);

        $this->assertTrue($this->check($p, kind: SpanKind::KIND_SERVER));
        $this->assertTrue($this->check($p, kind: SpanKind::KIND_CONSUMER));
        $this->assertFalse($this->check($p, kind: SpanKind::KIND_CLIENT));
    }

    public function test_parent_predicates(): void
    {
        $root = Context::getRoot();
        $local = Span::wrap(SpanContext::create(self::TRACE_ID, self::SPAN_ID))->storeInContext($root);
        $remote = Span::wrap(SpanContext::createFromRemoteParent(self::TRACE_ID, self::SPAN_ID))->storeInContext($root);

        $this->assertTrue($this->check(Predicate::isRootSpan(), $root));
        $this->assertFalse($this->check(Predicate::isRootSpan(), $local));
        $this->assertFalse($this->check(Predicate::isRootSpan(), $remote));

        $this->assertFalse($this->check(Predicate::hasLocalSpanParent(), $root));
        $this->assertTrue($this->check(Predicate::hasLocalSpanParent(), $local));
        $this->assertFalse($this->check(Predicate::hasLocalSpanParent(), $remote));

        // hasLocalParent() also matches root spans, unlike hasLocalSpanParent()
        $this->assertTrue($this->check(Predicate::hasLocalParent(), $root));
        $this->assertTrue($this->check(Predicate::hasLocalParent(), $local));
        $this->assertFalse($this->check(Predicate::hasLocalParent(), $remote));

        $this->assertFalse($this->check(Predicate::hasRemoteParent(), $root));
        $this->assertFalse($this->check(Predicate::hasRemoteParent(), $local));
        $this->assertTrue($this->check(Predicate::hasRemoteParent(), $remote));
    }

    public function test_any_span_and_boolean_combinators(): void
    {
        $true = Predicate::anySpan();
        $false = Predicate::not($true);
        $custom = new class() implements PredicateInterface {
            #[\Override]
            public function spanMatches(ContextInterface $parentContext, string $spanName, int $spanKind, AttributesInterface $attributes, array $links): bool
            {
                return true;
            }
        };

        $this->assertTrue($this->check($true));
        $this->assertFalse($this->check($false));
        $this->assertTrue($this->check(Predicate::and($true, $true)));
        $this->assertFalse($this->check(Predicate::and($true, $false)));
        $this->assertTrue($this->check(Predicate::or($false, $true)));
        $this->assertFalse($this->check(Predicate::or($false, $false)));
        $this->assertTrue($this->check(Predicate::not($false)));
        $this->assertTrue($this->check(Predicate::and($true, Predicate::or($false, Predicate::not($false)))));

        $this->assertSame('anySpan', (string) $true);
        $this->assertSame('not(anySpan)', (string) $false);
        $this->assertSame('and(anySpan,not(anySpan))', (string) Predicate::and($true, $false));
        $this->assertSame('or(anySpan,not(anySpan))', (string) Predicate::or($true, $false));
        // predicates that are not created by the factories are described by their class name
        $this->assertSame('not(' . $custom::class . ')', (string) Predicate::not($custom));
    }

    public function test_attribute_values(): void
    {
        $p = Predicate::attributeValues('http.response.status_code', ['404', '500']);

        $this->assertTrue($this->check($p, attributes: ['http.response.status_code' => 404]), 'int is compared by string representation');
        $this->assertFalse($this->check($p, attributes: ['http.response.status_code' => 200]));
        $this->assertFalse($this->check($p, attributes: []), 'missing attribute never matches');
        $this->assertTrue($this->check(Predicate::attributeValues('k', ['b']), attributes: ['k' => ['a', 'b']]), 'any array item may match');
        $this->assertTrue($this->check(Predicate::attributeValues('k', ['true']), attributes: ['k' => true]), 'bool is true/false');
    }

    /**
     * @param list<string> $included
     * @param list<string> $excluded
     */
    #[DataProvider('patternProvider')]
    public function test_attribute_patterns(array $included, array $excluded, string $value, bool $expected): void
    {
        $p = Predicate::attributePatterns('url.path', $included, $excluded);

        $this->assertSame($expected, $this->check($p, attributes: ['url.path' => $value]));
    }

    public static function patternProvider(): iterable
    {
        yield 'exact match' => [['/health'], [], '/health', true];
        yield 'exact is case-sensitive' => [['/health'], [], '/Health', false];
        yield 'star matches any sequence' => [['/api/*'], [], '/api/v1/users', true];
        yield 'star matches empty' => [['/api/*'], [], '/api/', true];
        yield 'star needs prefix' => [['/api/*'], [], '/other/api/x', false];
        yield 'question mark matches one char' => [['/v?/users'], [], '/v1/users', true];
        yield 'question mark does not match empty' => [['/v?/users'], [], '/v/users', false];
        yield 'regex chars are literal' => [['/a.b'], [], '/axb', false];
        yield 'regex chars literal match' => [['/a.b'], [], '/a.b', true];
        yield 'empty included includes all' => [[], [], '/anything', true];
        yield 'excluded wins over included' => [['/api/*'], ['/api/health*'], '/api/health/live', false];
        yield 'excluded only' => [[], ['/internal/*'], '/internal/x', false];
        yield 'excluded only, no match' => [[], ['/internal/*'], '/public/x', true];
    }

    public function test_attribute_patterns_exclusion_applies_to_invalid_utf8(): void
    {
        $p = Predicate::attributePatterns('k', [], ['*secret*']);

        $this->assertFalse($this->check($p, attributes: ['k' => "\xff-secret-\xfe"]), 'excluded even though the value is not valid UTF-8');
        $this->assertTrue($this->check($p, attributes: ['k' => "\xff-public-\xfe"]));
    }

    public function test_attribute_patterns_question_mark_matches_one_multibyte_character(): void
    {
        $this->assertTrue($this->check(Predicate::attributePatterns('k', ['caf?']), attributes: ['k' => 'café']));
    }

    public function test_attribute_patterns_missing_attribute_does_not_match(): void
    {
        $this->assertFalse($this->check(Predicate::attributePatterns('url.path', [], []), attributes: []));
    }

    private function check(Predicate $predicate, ?ContextInterface $context = null, int $kind = SpanKind::KIND_SERVER, array $attributes = []): bool
    {
        return $predicate->spanMatches($context ?? Context::getRoot(), 'span', $kind, Attributes::create($attributes), []);
    }
}
