<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace;

use Exception;
use OpenTelemetry\SDK\Registry;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOffSamplerFactory;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSamplerFactory;
use OpenTelemetry\SDK\Trace\SamplerFactory;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(SamplerFactory::class)]
class SamplerFactoryTest extends TestCase
{
    use TestState;

    #[DataProvider('samplerProvider')]
    public function test_create_sampler_from_environment(string $samplerName, string $expected, ?string $arg = null): void
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
    #[DataProvider('invalidSamplerProvider')]
    public function test_throws_exception_for_invalid_or_unsupported(?string $sampler, ?string $arg = null): void
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

    public function test_custom_sampler_can_be_registered_and_used(): void
    {
        // Register a custom sampler factory
        Registry::registerSamplerFactory('custom_test', AlwaysOffSamplerFactory::class);

        $this->setEnvironmentVariable('OTEL_TRACES_SAMPLER', 'custom_test');

        $factory = new SamplerFactory();
        $sampler = $factory->create();

        $this->assertStringContainsString('AlwaysOff', $sampler->getDescription());
    }

    public function test_custom_sampler_with_clobber_overrides_existing(): void
    {
        // First register a custom sampler
        Registry::registerSamplerFactory('clobber_test', AlwaysOnSamplerFactory::class);

        // Now override it with clobber=true
        Registry::registerSamplerFactory('clobber_test', AlwaysOffSamplerFactory::class, true);

        $this->setEnvironmentVariable('OTEL_TRACES_SAMPLER', 'clobber_test');

        $factory = new SamplerFactory();
        $sampler = $factory->create();

        // Should now be AlwaysOff due to override
        $this->assertStringContainsString('AlwaysOff', $sampler->getDescription());
    }

    public function test_custom_sampler_without_clobber_does_not_override(): void
    {
        // First register a custom sampler
        Registry::registerSamplerFactory('no_clobber_test', AlwaysOnSamplerFactory::class);

        // Try to override without clobber - should be ignored
        Registry::registerSamplerFactory('no_clobber_test', AlwaysOffSamplerFactory::class, false);

        $this->setEnvironmentVariable('OTEL_TRACES_SAMPLER', 'no_clobber_test');

        $factory = new SamplerFactory();
        $sampler = $factory->create();

        // Should still be AlwaysOn because clobber was false
        $this->assertStringContainsString('AlwaysOn', $sampler->getDescription());
    }
}
