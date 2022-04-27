<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Instrumentation;

use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScope;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScope
 */
class InstrumentationScopeTest extends TestCase
{
    public function test_get_empty(): void
    {
        $scope = InstrumentationScope::getEmpty();

        $this->assertEmpty($scope->getName());
        $this->assertNull($scope->getVersion());
        $this->assertNull($scope->getSchemaUrl());
    }

    public function test_getters(): void
    {
        $name = 'foo';
        $version = 'bar';
        $schemaUrl = 'http://baz';

        $scope = new InstrumentationScope($name, $version, $schemaUrl);

        $this->assertSame($name, $scope->getName());
        $this->assertSame($version, $scope->getVersion());
        $this->assertSame($schemaUrl, $scope->getSchemaUrl());
    }
}
