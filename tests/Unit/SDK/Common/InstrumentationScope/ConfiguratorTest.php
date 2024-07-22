<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Common\InstrumentationScope;

use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScope;
use OpenTelemetry\SDK\Common\InstrumentationScope\Configurator;
use OpenTelemetry\SDK\Common\InstrumentationScope\ConfiguratorBuilder;
use OpenTelemetry\SDK\Common\InstrumentationScope\Predicate;
use OpenTelemetry\SDK\Common\InstrumentationScope\State;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(Configurator::class)]
#[CoversClass(ConfiguratorBuilder::class)]
class ConfiguratorTest extends TestCase
{
    /** @var Predicate&MockObject */
    private Predicate $predicate;
    private Configurator $configurator;
    private InstrumentationScope $scope;

    public function setUp(): void
    {
        $this->scope = new InstrumentationScope('test', null, null, $this->createMock(AttributesInterface::class));
        $builder = new ConfiguratorBuilder();
        $this->predicate = $this->createMock(Predicate::class);
        $builder->addCondition($this->predicate, State::DISABLED);
        $this->configurator = $builder->build();
    }

    public function test_match(): void
    {
        $this->predicate->expects($this->once())->method('matches')->with($this->equalTo($this->scope))->willReturn(true);
        $config = $this->configurator->getConfig($this->scope);

        $this->assertFalse($config->isEnabled());
    }

    public function test_returns_default_on_no_match(): void
    {
        $this->predicate->expects($this->once())->method('matches')->with($this->equalTo($this->scope))->willReturn(false);
        $config = $this->configurator->getConfig($this->scope);

        $this->assertTrue($config->isEnabled());
    }

    public function test_builder(): void
    {
        $this->assertInstanceOf(ConfiguratorBuilder::class, Configurator::builder());
    }
}
