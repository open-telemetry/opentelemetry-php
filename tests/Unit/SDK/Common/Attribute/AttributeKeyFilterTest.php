<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Attribute;

use OpenTelemetry\SDK\Common\Attribute\AttributeKeyFilter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(AttributeKeyFilter::class)]
final class AttributeKeyFilterTest extends TestCase
{
    /**
     * @param list<string>|null $included
     * @param list<string>|null $excluded
     */
    #[DataProvider('keyProvider')]
    public function test_accepts(?array $included, ?array $excluded, string $key, bool $expected): void
    {
        $filter = new AttributeKeyFilter($included, $excluded);

        $this->assertSame($expected, $filter->accepts($key));
        $this->assertSame($expected, $filter->accepts($key), 'cached result');
    }

    public static function keyProvider(): iterable
    {
        yield 'neither keeps all' => [null, null, 'foo', true];
        yield 'exact included' => [['foo'], null, 'foo', true];
        yield 'not included' => [['foo'], null, 'bar', false];
        yield 'star included' => [['foo*'], null, 'foobar', true];
        yield 'star matches none' => [['foo*'], null, 'foo', true];
        yield 'question mark matches one' => [['fo?'], null, 'fob', true];
        yield 'question mark needs one' => [['fo?'], null, 'fo', false];
        yield 'regex characters are literal' => [['a.b'], null, 'axb', false];
        yield 'delimiter is literal' => [['a/b'], null, 'a/b', true];
        yield 'case sensitive' => [['foo'], null, 'FOO', false];
        yield 'star matches newline' => [['a*'], null, "a\nb", true];
        yield 'trailing newline is not ignored' => [['foo'], null, "foo\n", false];
        yield 'excluded' => [null, ['foo*'], 'foobar', false];
        yield 'excluded wins' => [['foo*'], ['foobar'], 'foobar', false];
        yield 'empty included keeps none' => [[], null, 'foo', false];
        yield 'empty excluded drops none' => [null, [], 'foo', true];
    }

    public function test_caches_decision_per_key(): void
    {
        $filter = new AttributeKeyFilter(['foo*'], null);

        $accepted = new \ReflectionProperty($filter, 'accepted');
        $accepted->setValue($filter, ['foo' => false]);

        $this->assertFalse($filter->accepts('foo'));
    }

    public function test_filter_keeps_accepted_keys(): void
    {
        $this->assertSame(
            ['foo.a' => 1, 'bar.a' => 3],
            (new AttributeKeyFilter(['*.a', 'foo.*'], ['foo.b']))->filter(['foo.a' => 1, 'foo.b' => 2, 'bar.a' => 3, 'bar.b' => 4]),
        );
    }

    public function test_numeric_keys_are_matched_as_strings(): void
    {
        $this->assertSame([1 => 'x'], (new AttributeKeyFilter(['1'], null))->filter([1 => 'x', 2 => 'y']));
    }
}
