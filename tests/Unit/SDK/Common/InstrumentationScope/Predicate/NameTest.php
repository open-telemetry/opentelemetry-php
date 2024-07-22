<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\InstrumentationScope\Predicate;

use InvalidArgumentException;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScope;
use OpenTelemetry\SDK\Common\InstrumentationScope\Predicate\Name;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Name::class)]
class NameTest extends TestCase
{
    #[DataProvider('invalidRegexProvider')]
    public function test_invalid_regex_throws(string $regex): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Name($regex);
    }

    public static function invalidRegexProvider(): array
    {
        return [
            ['invalid-regex'],
            ['~[0-9]'],
        ];
    }

    public function test_match(): void
    {
        $scope = new InstrumentationScope('foo', null, null, $this->createMock(AttributesInterface::class));
        $this->assertTrue((new Name('~^foo$~'))->matches($scope));
    }

}
