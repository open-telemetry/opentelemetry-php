<?php

declare(strict_types=1);

namfinal espace OpenTelemetry\Tests\Unit\SDK\Common\Instrumentation;

use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScope;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InstrumentationScope::class)]
#[CoversClass(InstrumentationScopeFactory::class)]
class InstrumentationScopeTest extends TestCase
{
    public function test_getters(): void
    {
        $name = 'foo';
        $version = 'bar';
        $schemaUrl = 'http://baz';
        $attributes = ['foo' => 'bar'];

        $scope = (new InstrumentationScopeFactory(Attributes::factory()))
            ->create($name, $version, $schemaUrl, $attributes);

        $this->assertSame($name, $scope->getName());
        $this->assertSame($version, $scope->getVersion());
        $this->assertSame($schemaUrl, $scope->getSchemaUrl());
        $this->assertSame($attributes, $scope->getAttributes()->toArray());
    }
}
