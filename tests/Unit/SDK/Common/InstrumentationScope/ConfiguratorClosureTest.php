<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\InstrumentationScope;

use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Common\InstrumentationScope\ConfiguratorClosure;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConfiguratorClosure::class)]
final class ConfiguratorClosureTest extends TestCase
{
    public function test_matches_returns_true_when_all_null(): void
    {
        $closure = static fn () => null;
        $configurator = new ConfiguratorClosure($closure, null, null, null);

        $scope = $this->createMock(InstrumentationScopeInterface::class);
        $scope->method('getName')->willReturn('anything');
        $scope->method('getVersion')->willReturn('1.0');
        $scope->method('getSchemaUrl')->willReturn('https://example.com');

        $this->assertTrue($configurator->matches($scope));
    }

    public function test_matches_returns_true_when_name_regex_matches(): void
    {
        $closure = static fn () => null;
        $configurator = new ConfiguratorClosure($closure, '/^my-lib/', null, null);

        $scope = $this->createMock(InstrumentationScopeInterface::class);
        $scope->method('getName')->willReturn('my-lib-component');

        $this->assertTrue($configurator->matches($scope));
    }

    public function test_matches_returns_false_when_name_regex_does_not_match(): void
    {
        $closure = static fn () => null;
        $configurator = new ConfiguratorClosure($closure, '/^my-lib/', null, null);

        $scope = $this->createMock(InstrumentationScopeInterface::class);
        $scope->method('getName')->willReturn('other-lib');

        $this->assertFalse($configurator->matches($scope));
    }

    public function test_matches_returns_true_when_version_matches(): void
    {
        $closure = static fn () => null;
        $configurator = new ConfiguratorClosure($closure, null, '2.0', null);

        $scope = $this->createMock(InstrumentationScopeInterface::class);
        $scope->method('getVersion')->willReturn('2.0');

        $this->assertTrue($configurator->matches($scope));
    }

    public function test_matches_returns_false_when_version_does_not_match(): void
    {
        $closure = static fn () => null;
        $configurator = new ConfiguratorClosure($closure, null, '2.0', null);

        $scope = $this->createMock(InstrumentationScopeInterface::class);
        $scope->method('getName')->willReturn('x');
        $scope->method('getVersion')->willReturn('1.0');

        $this->assertFalse($configurator->matches($scope));
    }

    public function test_matches_returns_true_when_schema_url_matches(): void
    {
        $closure = static fn () => null;
        $configurator = new ConfiguratorClosure($closure, null, null, 'https://example.com/schema');

        $scope = $this->createMock(InstrumentationScopeInterface::class);
        $scope->method('getName')->willReturn('x');
        $scope->method('getSchemaUrl')->willReturn('https://example.com/schema');

        $this->assertTrue($configurator->matches($scope));
    }

    public function test_matches_returns_false_when_schema_url_does_not_match(): void
    {
        $closure = static fn () => null;
        $configurator = new ConfiguratorClosure($closure, null, null, 'https://example.com/schema');

        $scope = $this->createMock(InstrumentationScopeInterface::class);
        $scope->method('getName')->willReturn('x');
        $scope->method('getSchemaUrl')->willReturn('https://other.com/schema');

        $this->assertFalse($configurator->matches($scope));
    }

    public function test_closure_property_is_accessible(): void
    {
        $closure = static fn () => 'result';
        $configurator = new ConfiguratorClosure($closure, null, null, null);

        $this->assertSame($closure, $configurator->closure);
    }
}
