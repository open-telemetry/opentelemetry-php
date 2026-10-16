<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Util;

use OpenTelemetry\SDK\Common\Util\WildcardListMatcher;
use OpenTelemetry\SDK\Common\Util\WildcardMatcher;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(WildcardListMatcher::class)]
#[CoversClass(WildcardMatcher::class)]
class WildcardListMatcherTest extends TestCase
{
    /**
     * @param string[] $exprs
     */
    #[DataProvider('provider')]
    public function test_match(array $exprs, string $text, ?string $expected): void
    {
        $this->assertSame($expected, (new WildcardListMatcher($exprs))->match($text));
    }

    public static function provider(): array
    {
        return [
            'prefix' => [['/users/*'], '/users/42', '/users/*'],
            'suffix' => [['*.php'], '/a/b.php', '*.php'],
            'exact' => [['/health'], '/health', '/health'],
            'exact no match' => [['/health'], '/healthz', null],
            'case insensitive' => [['/USERS/*'], '/users/1', '/USERS/*'],
            'case sensitive' => [['(?-i)/USERS/*'], '/users/1', null],
            'first wins' => [['/a/*', '/a/b*'], '/a/b', '/a/*'],
            'empty list' => [[], '/x', null],
        ];
    }

    #[DataProvider('stringProvider')]
    public function test_to_string_and_group_name(string $expr, string $groupName, string $string): void
    {
        $matcher = new WildcardMatcher($expr);

        $this->assertSame($groupName, $matcher->groupName());
        $this->assertSame($string, (string) $matcher);
    }

    public static function stringProvider(): array
    {
        return [
            'prefix' => ['/users/*', '/users/*', '/users/*'],
            'infix' => ['/a/*/b', '/a/*/b', '/a/*/b'],
            'both ends' => ['*mid*', '*mid*', '*mid*'],
            'case sensitive' => ['(?-i)/Users/*', '/Users/*', '(?-i)/Users/*'],
        ];
    }

    public function test_list_to_string(): void
    {
        $this->assertSame('/a/*, (?-i)/B', (string) new WildcardListMatcher(['/a/*', '(?-i)/B']));
    }

    #[DataProvider('matchProvider')]
    public function test_matcher(string $expr, string $text, bool $expected): void
    {
        $this->assertSame($expected, (new WildcardMatcher($expr))->match($text));
    }

    public static function matchProvider(): array
    {
        return [
            'infix match' => ['/a/*/b', '/a/x/y/b', true],
            'infix mismatch' => ['/a/*/b', '/a/x/y/c', false],
            'prefix must start' => ['/a/*', 'x/a/1', false],
            'contains' => ['*mid*', 'xx-mid-yy', true],
            'only wildcard matches anything' => ['*', 'anything', true],
            'empty expression matches empty text' => ['', '', true],
            'empty expression does not match text' => ['', 'x', false],
        ];
    }
}
