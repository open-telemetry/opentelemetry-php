<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Util;

use OpenTelemetry\SDK\Common\Util\WildcardListMatcher;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(WildcardListMatcher::class)]
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
}
