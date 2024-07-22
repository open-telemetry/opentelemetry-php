<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\InstrumentationScope\Predicate;

use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Common\InstrumentationScope\Predicate\Attribute;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Attribute::class)]
class AttributeTest extends TestCase
{
    public function test_match(): void
    {
        $attributes = new Attributes(['foo' => 'bar'], 0);
        $scope = $this->createMock(InstrumentationScopeInterface::class);
        $scope->method('getAttributes')->willReturn($attributes);

        $predicate = new Attribute('foo', 'bar');
        $this->assertTrue($predicate->matches($scope), 'found and matches');

        $predicate = new Attribute('foo', 'baz');
        $this->assertFalse($predicate->matches($scope), 'found but does not match');

        $predicate = new Attribute('bar', 'bat');
        $this->assertFalse($predicate->matches($scope), 'no attribute found');
    }
}
