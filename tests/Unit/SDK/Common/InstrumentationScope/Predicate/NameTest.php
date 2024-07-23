<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\InstrumentationScope\Predicate;

use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScope;
use OpenTelemetry\SDK\Common\InstrumentationScope\Predicate\Name;
use OpenTelemetry\SemConv\Version;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Name::class)]
class NameTest extends TestCase
{
    #[DataProvider('nameProvider')]
    public function test_match(string $pattern, bool $expected): void
    {
        $scope = new InstrumentationScope(
            'io.opentelemetry.php.example.foo',
            '1.0',
            Version::VERSION_1_26_0->url(),
            $this->createMock(AttributesInterface::class),
        );
        $this->assertSame($expected, (new Name($pattern))->matches($scope));
    }

    public static function nameProvider(): array
    {
        return [
            ['io.opentelemetry.php.example.foo', true],
            ['io.opentelemetry.php.example.*', true],
            ['io.opentelemetry.php.*', true],
            ['*', true],
            ['foo', false],
            ['*.foo', true],
            ['*.bar', false],
            ['*.?oo', true],
        ];
    }
}
