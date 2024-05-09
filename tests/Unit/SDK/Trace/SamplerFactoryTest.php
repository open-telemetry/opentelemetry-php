<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace;

use Exception;
use OpenTelemetry\SDK\Trace\SamplerFactory;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass OpenTelemetry\SDK\Trace\SamplerFactory
 */
class SamplerFactoryTest extends TestCase
{
    use TestState;

    /**
     * @covers ::create
     * @dataProvider samplerProvider
     */
    public function test_create_sampler_from_environment(string $samplerName, string $expected, string $arg = null): void
    {
        $this->setEnvironmentVariable('OTEL_TRACES_SAMPLER', $samplerName);
        $this->setEnvironmentVariable('OTEL_TRACES_SAMPLER_ARG', $arg);
        $factory = new SamplerFactory();
        $sampler = $factory->create();
        $this->assertStringContainsString($expected, $sampler->getDescription());
    }

    public static function samplerProvider(): array
    {
        return [
            'default sampler' => ['', 'ParentBased+AlwaysOn'],
            'always on' => ['always_on', 'AlwaysOn'],
            'always off' => ['always_off', 'AlwaysOff'],
            'trace id ratio' => ['traceidratio', 'TraceIdRatio', '0.95'],
            'parent based always on' => ['parentbased_always_on', 'ParentBased+AlwaysOn'],
            'parent based always off' => ['parentbased_always_off', 'ParentBased+AlwaysOff'],
            'parent based trade id ratio' => ['parentbased_traceidratio', 'ParentBased+TraceIdRatio', '0.95'],
        ];
    }
    /**
     * @covers ::create
     * @dataProvider invalidSamplerProvider
     */
    public function test_throws_exception_for_invalid_or_unsupported(?string $sampler, string $arg = null): void
    {
        $this->setEnvironmentVariable('OTEL_TRACES_SAMPLER', $sampler);
        $this->setEnvironmentVariable('OTEL_TRACES_SAMPLER_ARG', $arg);
        $factory = new SamplerFactory();
        $this->expectException(Exception::class);
        $factory->create();
    }

    public static function invalidSamplerProvider(): array
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
