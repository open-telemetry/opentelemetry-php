<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Trace;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use Exception;
use OpenTelemetry\SDK\Trace\SamplerFactory;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use PHPUnit\Framework\TestCase;

class SamplerFactoryTest extends TestCase
{
    use EnvironmentVariables;

    protected function tearDown(): void
    {
        $this->restoreEnvironmentVariables();
    }

    /**
     * @test
     * @dataProvider samplerProvider
     */
    public function samplerFactory_createSamplerFromEnvironment(string $sampler, string $arg = null)
    {
        $this->setEnvironmentVariable('OTEL_TRACES_SAMPLER', $sampler);
        $this->setEnvironmentVariable('OTEL_TRACES_SAMPLER_ARG', $arg);
        $factory = new SamplerFactory();
        $this->assertInstanceOf(SamplerInterface::class, $factory->fromEnvironment());
    }

    public function samplerProvider()
    {
        return [
            'always on' => ['always_on'],
            'always off' => ['always_off'],
            'trace id ratio' => ['traceidratio', '0.95'],
            'parent based always on' => ['parentbased_always_on'],
            'parent based always off' => ['parentbased_always_off'],
            'parent based trade id ratio' => ['parentbased_traceidratio', '0.95'],
        ];
    }
    /**
     * @test
     * @dataProvider invalidSamplerProvider
     */
    public function samplerFactory_throwsExceptionForInvalidOrUnsupported(string $sampler, string $arg = null)
    {
        $this->setEnvironmentVariable('OTEL_TRACES_SAMPLER', $sampler);
        $this->setEnvironmentVariable('OTEL_TRACES_SAMPLER_ARG', $arg);
        $factory = new SamplerFactory();
        $this->expectException(Exception::class);
        $factory->fromEnvironment();
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
