<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Trace;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use Exception;
use OpenTelemetry\SDK\ConfigBuilder;
use OpenTelemetry\SDK\Trace\SamplerFactory;
use PHPUnit\Framework\TestCase;

class SamplerFactoryTest extends TestCase
{
    use EnvironmentVariables;

    private ConfigBuilder $configBuilder;

    protected function setUp(): void
    {
        $this->configBuilder = new ConfigBuilder();
    }

    protected function tearDown(): void
    {
        $this->restoreEnvironmentVariables();
    }

    /**
     * @test
     * @dataProvider samplerProvider
     */
    public function samplerFactory_createSamplerFromEnvironment(string $samplerName, string $expected, string $arg = null)
    {
        $this->setEnvironmentVariable('OTEL_TRACES_SAMPLER', $samplerName);
        $this->setEnvironmentVariable('OTEL_TRACES_SAMPLER_ARG', $arg);
        $factory = new SamplerFactory();
        $sampler = $factory->fromConfig($this->configBuilder->build());
        $this->assertStringContainsString($expected, $sampler->getDescription());
    }

    public function samplerProvider()
    {
        return [
            'always on' => ['always_on', 'AlwaysOn'],
            'always off' => ['always_off', 'AlwaysOff'],
            'trace id ratio' => ['traceidratio', 'TraceIdRatio', '0.95'],
            'parent based always on' => ['parentbased_always_on', 'ParentBased+AlwaysOn'],
            'parent based always off' => ['parentbased_always_off', 'ParentBased+AlwaysOff'],
            'parent based trade id ratio' => ['parentbased_traceidratio', 'ParentBased+TraceIdRatio', '0.95'],
            'not set' => ['', 'ParentBased+AlwaysOn'],
        ];
    }
    /**
     * @test
     * @dataProvider invalidSamplerProvider
     */
    public function samplerFactory_throwsExceptionForInvalidOrUnsupported(?string $sampler, string $arg = null)
    {
        $this->setEnvironmentVariable('OTEL_TRACES_SAMPLER', $sampler);
        $this->setEnvironmentVariable('OTEL_TRACES_SAMPLER_ARG', $arg);
        $factory = new SamplerFactory();
        $this->expectException(Exception::class);
        $factory->fromConfig($this->configBuilder->build());
    }

    public function invalidSamplerProvider()
    {
        return [
            'ratio without arg' => ['traceidratio'],
            'parent ratio without arg' => ['parentbased_traceidratio'],
            'ratio with invalid arg' => ['traceidratio', 'foo'],
            'parent ratio with invalid arg' => ['parentbased_traceidratio', 'foo'],
            'unknown sampler' => ['foo'],
        ];
    }
}
