<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Common\InstrumentationScope;

use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScope;
use OpenTelemetry\SDK\Common\InstrumentationScope\Config;
use OpenTelemetry\SDK\Common\InstrumentationScope\Configurator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Configurator::class)]
class ConfiguratorTest extends TestCase
{
    private Configurator $configurator;
    private InstrumentationScope $scope;

    #[\Override]
    public function setUp(): void
    {
        $config = new class() implements Config {
            private bool $disabled = false;
            #[\Override]
            public function setDisabled(bool $disabled): void
            {
                $this->disabled = $disabled;
            }
            #[\Override]
            public function isEnabled(): bool
            {
                return $this->disabled === false;
            }
        };
        $this->scope = new InstrumentationScope('test', '1.0', 'https://example.org/schema', $this->createMock(AttributesInterface::class));
        $this->configurator = (new Configurator(static fn () => $config));
    }

    public function test_match_name(): void
    {
        $configurator = $this->configurator->with(static fn (Config $config) => $config->setDisabled(true), name: 'test');
        $this->assertFalse($configurator->resolve($this->scope)->isEnabled());
    }

    public function test_returns_default_on_no_match(): void
    {
        $config = $this->configurator->resolve($this->scope);

        $this->assertTrue($config->isEnabled());
    }

    public function test_match_wildcard_name(): void
    {
        $configurator = $this->configurator->with(static fn (Config $config) => $config->setDisabled(true), name: 'te*');
        $this->assertFalse($configurator->resolve($this->scope)->isEnabled());
    }

    public function test_no_match_wildcard_name(): void
    {
        $configurator = $this->configurator->with(static fn (Config $config) => $config->setDisabled(true), name: 'other*');
        $this->assertTrue($configurator->resolve($this->scope)->isEnabled());
    }

    public function test_match_null_name(): void
    {
        $configurator = $this->configurator->with(static fn (Config $config) => $config->setDisabled(true), name: null);
        $this->assertFalse($configurator->resolve($this->scope)->isEnabled());
    }

    public function test_resolve_caches_config(): void
    {
        $config1 = $this->configurator->resolve($this->scope);
        $config2 = $this->configurator->resolve($this->scope);
        $this->assertSame($config1, $config2);
    }

    public function test_with_applies_to_already_resolved_config(): void
    {
        $config = $this->configurator->resolve($this->scope);
        $this->assertTrue($config->isEnabled());
        $this->configurator->with(static fn (Config $config) => $config->setDisabled(true), name: 'test');
        $this->assertFalse($config->isEnabled());
    }

    public function test_logger_factory(): void
    {
        $configurator = Configurator::logger();
        $this->assertInstanceOf(Configurator::class, $configurator);
    }

    public function test_meter_factory(): void
    {
        $configurator = Configurator::meter();
        $this->assertInstanceOf(Configurator::class, $configurator);
    }

    public function test_tracer_factory(): void
    {
        $configurator = Configurator::tracer();
        $this->assertInstanceOf(Configurator::class, $configurator);
    }
}
