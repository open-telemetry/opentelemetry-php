<?php

declare(strict_types=1);

namespace Unit\SDK\Common\InstrumentationScope\Predicate;

use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Common\InstrumentationScope\Predicate\AttributeExists;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AttributeExists::class)]
class AttributeExistsTest extends TestCase
{
    public function test_match(): void
    {
        $scope = $this->createMock(InstrumentationScopeInterface::class);
        $attributes = $this->createMock(AttributesInterface::class);
        $scope->method('getAttributes')->willReturn($attributes);
        $attributes->method('has')->willReturnCallback(function (string $key) {
            return $key === 'foo';
        });

        $predicate = new AttributeExists('foo');
        $this->assertTrue($predicate->matches($scope));

        $predicate = new AttributeExists('bar');
        $this->assertFalse($predicate->matches($scope));
    }
}
