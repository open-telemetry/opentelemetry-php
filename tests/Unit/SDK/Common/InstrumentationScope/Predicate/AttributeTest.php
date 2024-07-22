<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\InstrumentationScope\Predicate;

use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Common\InstrumentationScope\Predicate;
use OpenTelemetry\SDK\Common\InstrumentationScope\Predicate\Attribute;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Attribute::class)]
class AttributeTest extends TestCase
{
    #[DataProvider('matchProvider')]
    public function test_matches(Predicate $predicate, bool $expected): void
    {
        $attributes = new Attributes(['foo' => 'bar'], 0);
        $scope = $this->createMock(InstrumentationScopeInterface::class);
        $scope->method('getAttributes')->willReturn($attributes);

        $this->assertSame($expected, $predicate->matches($scope));
    }

    public static function matchProvider(): array
    {
        return [
            'found and matches' => [
                new Attribute('foo', 'bar'),
                true,
            ],
            'found but does not match' => [
                new Attribute('foo', 'baz'),
                false,
            ],
            'no attribute found' => [
                new Attribute('bar', 'bat'),
                false,
            ],
        ];
    }

    public function test_construct(): void
    {
        $p = new Attribute('foo', 'bar');
        $this->assertInstanceOf(Attribute::class, $p);
    }
}
